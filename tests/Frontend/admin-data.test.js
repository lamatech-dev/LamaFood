import test from 'node:test';
import assert from 'node:assert/strict';
import { buildReadyBlockTranslations, buildReadyTranslations, nextAvailability, parseSchemaValue, requiresTableKey, swapOrder, loadCollection, collectionPage, hasPermission, saveProductAccess, filterAdminCollection, mediaLabel, adminErrorMessage } from '../../resources/js/admin-data.js';

test('builds locale-driven independent ready translations', () => {
    const result = buildReadyTranslations(
        [{ locale: 'fa' }, { locale: 'en' }, { locale: 'ar' }],
        { fa: { name: 'قهوه' }, en: { name: 'Coffee' }, ar: { name: 'قهوة' } },
    );

    assert.deepEqual(Object.keys(result), ['fa', 'en', 'ar']);
    assert.equal(result.ar.name, 'قهوة');
    assert.equal(result.en.translation_state, 'ready');
});

test('availability toggle never changes publication state', () => {
    assert.equal(nextAvailability('available'), 'sold_out');
    assert.equal(nextAvailability('sold_out'), 'available');
});

test('builds explicit locale-separated CMS block content', () => {
    const result = buildReadyBlockTranslations(
        [{ locale: 'fa' }, { locale: 'en' }, { locale: 'ar' }],
        { fa: { title: 'دناردی' }, en: { title: 'Denardi' }, ar: { title: 'ديناردي' } },
    );

    assert.deepEqual(result.ar.content, { title: 'ديناردي' });
    assert.equal(result.fa.translation_state, 'ready');
});

test('parses typed CMS structure values without locale JSON', () => {
    assert.equal(parseSchemaValue('12', 'integer?'), 12);
    assert.deepEqual(parseSchemaValue('2, 7', 'integer[]?'), [2, 7]);
    assert.equal(parseSchemaValue('', 'string?'), null);
});

test('only table QR requires a table key in V1', () => {
    assert.equal(requiresTableKey('table'), true);
    assert.equal(requiresTableKey('menu'), false);
    assert.equal(requiresTableKey('campaign'), false);
});

test('reorders management rows within safe boundaries', () => {
    assert.deepEqual(swapOrder(['a', 'b', 'c'], 1, -1), ['b', 'a', 'c']);
    assert.deepEqual(swapOrder(['a', 'b', 'c'], 0, -1), ['a', 'b', 'c']);
    assert.deepEqual(swapOrder(['a', 'b', 'c'], 2, 1), ['a', 'b', 'c']);
});

test('loads products beyond 50 and media beyond 30 without trusting next-page URLs', async () => {
    for (const pageSize of [50, 30]) {
        const calls = [];
        const result = await loadCollection(async (path) => {
            calls.push(path);
            const page = Number(path.split('page=')[1]);
            return { data: Array.from({ length: page === 1 ? pageSize : 1 }, (_, index) => (page - 1) * pageSize + index + 1), current_page: page, last_page: 2, next_page_url: 'https://untrusted.example' };
        }, '/items');
        assert.equal(result.length, pageSize + 1);
        assert.equal(result.at(-1), pageSize + 1);
        assert.deepEqual(calls, ['/items?page=1', '/items?page=2']);
    }
});

test('fails closed on malformed pagination and does not present a partial list after failure', async () => {
    await assert.rejects(loadCollection(async () => ({ data: [], current_page: 0, last_page: 2 }), '/products'));
    await assert.rejects(loadCollection(async (path) => {
        if (path.endsWith('page=2')) throw new Error('Network failure');
        return { data: [1], current_page: 1, last_page: 2 };
    }, '/products'), /Network failure/);
    assert.deepEqual(await loadCollection(async () => ({ data: [], current_page: 1, last_page: 1 }), '/media'), []);
});

test('paginates complete collections and clamps page after deletion without losing selector data', () => {
    const items = Array.from({ length: 51 }, (_, index) => index + 1);
    assert.deepEqual(collectionPage(items, 3), { items: [41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51], page: 3, pages: 3, total: 51 });
    assert.deepEqual(collectionPage([], 3), { items: [], page: 1, pages: 1, total: 0 });
    assert.equal(collectionPage(items.slice(0, 20), 3).page, 1);
    assert.equal(items.length, 51);
});

test('permissions fail closed and use existing permission names rather than role or username shortcuts', () => {
    assert.equal(hasPermission(null, 'menu.edit'), false);
    assert.equal(hasPermission({ username: 'godfather', permissions: [] }, 'menu.edit'), false);
    assert.equal(hasPermission({ permissions: ['menu.edit'] }, 'menu.edit'), true);
    assert.equal(hasPermission({ permissions: ['menu.edit'] }, 'menu.price'), false);
});

test('content editor can save publication without issuing forbidden branch pricing requests', async () => {
    const calls = [];
    await saveProductAccess(async (...args) => calls.push(args), { permissions: ['menu.edit', 'menu.publish'] }, { public_id: 'coffee', publication_state: 'draft' }, { publication_state: 'published', branch_id: 1, price_amount: 100, availability_state: 'available' });
    assert.equal(calls.length, 1);
    assert.equal(calls[0][0], '/products/coffee/publication-state');
    assert.deepEqual(JSON.parse(calls[0][1].body), { publication_state: 'published' });
});

test('availability-only updates preserve price and version and cannot create an unpriced setting', async () => {
    const calls = [];
    const user = { permissions: ['menu.availability'] };
    const product = { public_id: 'coffee', publication_state: 'draft' };
    const desired = { publication_state: 'published', branch_id: 2, price_amount: 999, availability_state: 'sold_out' };
    await saveProductAccess(async (...args) => calls.push(args), user, product, desired, { price_amount: 0, availability_state: 'available', version: 4 });
    assert.equal(calls.length, 1);
    assert.deepEqual(JSON.parse(calls[0][1].body), { price_amount: 0, availability_state: 'sold_out', expected_version: 4 });
    await saveProductAccess(async (...args) => calls.push(args), user, product, desired);
    assert.equal(calls.length, 1);
});

test('price-only users preserve availability and owners can initialize branch settings', async () => {
    const calls = [];
    const api = async (...args) => calls.push(args);
    const product = { public_id: 'coffee', publication_state: 'draft' };
    const desired = { publication_state: 'draft', branch_id: 1, price_amount: 200, availability_state: 'available' };
    await saveProductAccess(api, { permissions: ['menu.price'] }, product, desired, { price_amount: 100, availability_state: 'sold_out', version: 2 });
    assert.deepEqual(JSON.parse(calls[0][1].body), { price_amount: 200, availability_state: 'sold_out', expected_version: 2 });
    await saveProductAccess(api, { permissions: ['menu.price', 'menu.availability', 'menu.publish'] }, product, desired);
    assert.deepEqual(JSON.parse(calls[1][1].body), { price_amount: 200, availability_state: 'available', expected_version: 0 });
});

test('admin search matches three languages and combines category, publication and branch availability', () => {
    const items = [
        { public_id: 'one', slug: 'coffee', translations: [{ name: 'کیک' }, { name: 'Coffee' }, { name: 'قهوة' }], category: { public_id: 'drinks' }, publication_state: 'published', branch_settings: [{ branch_id: 1, availability_state: 'sold_out' }, { branch_id: 2, availability_state: 'available' }] },
        { public_id: 'two', slug: 'tea', translations: [{ name: 'Tea' }], category: { public_id: 'drinks' }, publication_state: 'draft', branch_settings: [] },
    ];
    assert.equal(filterAdminCollection(items, { query: 'كيك' })[0].public_id, 'one');
    assert.equal(filterAdminCollection(items, { query: ' COFFEE ' })[0].public_id, 'one');
    assert.equal(filterAdminCollection(items, { query: 'قهوة' })[0].public_id, 'one');
    assert.equal(filterAdminCollection(items, { category: 'drinks', status: 'published', availability: 'sold_out', branchId: 1 }).length, 1);
    assert.equal(filterAdminCollection(items, { availability: 'available', branchId: 1 }).length, 0);
    assert.equal(filterAdminCollection(items, { query: 'missing' }).length, 0);
    assert.equal(items.length, 2);
});

test('media search and selection labels use localized title then alt then safe identifier', () => {
    const media = { public_id: 'image-31', translations: [{ locale: 'fa', title: '', alt: 'قهوه' }, { locale: 'en', title: 'Coffee image' }] };
    assert.equal(mediaLabel(media, 'fa'), 'قهوه');
    assert.equal(mediaLabel(media, 'en'), 'Coffee image');
    assert.equal(mediaLabel(media, 'ar'), 'image-31');
    assert.deepEqual(filterAdminCollection([media], { query: 'قهوه' }), [media]);
});

test('admin validation explains untranslated keys and preserves explicit server messages', () => {
    assert.equal(adminErrorMessage({ errors: { slug: ['validation.alpha_dash'] } }), 'شناسه URL فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.');
    assert.equal(adminErrorMessage({ errors: { 'translations.fa.name': ['validation.required'] } }), 'نام الزامی است.');
    assert.equal(adminErrorMessage({ message: 'Conflict', errors: { slug: ['Already used'] } }), 'Already used');
    assert.equal(adminErrorMessage({ errors: { custom: ['validation.custom'] } }), 'این مقدار معتبر نیست؛ مقدار واردشده را بررسی کنید.');
    assert.equal(adminErrorMessage({ message: 'Permission denied' }), 'Permission denied');
});

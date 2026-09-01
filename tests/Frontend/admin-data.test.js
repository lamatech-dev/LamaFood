import test from 'node:test';
import assert from 'node:assert/strict';
import { buildReadyBlockTranslations, buildReadyTranslations, nextAvailability, parseSchemaValue, requiresTableKey } from '../../resources/js/admin-data.js';

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

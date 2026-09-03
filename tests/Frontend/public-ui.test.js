import assert from 'node:assert/strict';
import test from 'node:test';

import { activeCategoryId, normalizeSearchText, productMatchesSearch } from '../../resources/js/public-ui.js';

test('normalizes localized menu search text', () => {
    assert.equal(normalizeSearchText('  CAPPUCCINO  ', 'en'), 'cappuccino');
    assert.equal(normalizeSearchText('  کاپوچینو  ', 'fa'), 'کاپوچینو');
});

test('scrollspy selects the latest section above the sticky navigation boundary', () => {
    const sections = [{ id: 'coffee', top: -600 }, { id: 'tea', top: 200 }, { id: 'pastry', top: 800 }];
    assert.equal(activeCategoryId(sections, 248), 'tea');
    assert.equal(activeCategoryId(sections, 200), 'tea');
    assert.equal(activeCategoryId(sections, 150), 'coffee');
});

test('scrollspy handles the start, last section and empty search results', () => {
    assert.equal(activeCategoryId([{ id: 'coffee', top: 300 }], 200), 'coffee');
    assert.equal(activeCategoryId([{ id: 'pastry', top: -2000 }], 200), 'pastry');
    assert.equal(activeCategoryId([], 200), null);
});

test('matches product names and descriptions without language mixing', () => {
    assert.equal(productMatchesSearch('کاپوچینو اسپرسو و شیر', 'کاپو', 'fa'), true);
    assert.equal(productMatchesSearch('Cappuccino espresso and milk', 'coffee', 'en'), false);
});

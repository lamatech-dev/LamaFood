import assert from 'node:assert/strict';
import test from 'node:test';

import { normalizeSearchText, productMatchesSearch } from '../../resources/js/public-ui.js';

test('normalizes localized menu search text', () => {
    assert.equal(normalizeSearchText('  CAPPUCCINO  ', 'en'), 'cappuccino');
    assert.equal(normalizeSearchText('  کاپوچینو  ', 'fa'), 'کاپوچینو');
});

test('matches product names and descriptions without language mixing', () => {
    assert.equal(productMatchesSearch('کاپوچینو اسپرسو و شیر', 'کاپو', 'fa'), true);
    assert.equal(productMatchesSearch('Cappuccino espresso and milk', 'coffee', 'en'), false);
});

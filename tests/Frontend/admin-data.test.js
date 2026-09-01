import test from 'node:test';
import assert from 'node:assert/strict';
import { buildReadyTranslations, nextAvailability } from '../../resources/js/admin-data.js';

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

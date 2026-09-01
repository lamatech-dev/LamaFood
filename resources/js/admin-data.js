export function buildReadyTranslations(locales, values) {
    return Object.fromEntries(locales.map(({ locale }) => [locale, {
        ...values[locale],
        translation_state: 'ready',
    }]));
}

export function nextAvailability(current) {
    return current === 'sold_out' ? 'available' : 'sold_out';
}

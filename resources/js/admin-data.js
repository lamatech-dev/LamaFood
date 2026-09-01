export function buildReadyTranslations(locales, values) {
    return Object.fromEntries(locales.map(({ locale }) => [locale, {
        ...values[locale],
        translation_state: 'ready',
    }]));
}

export function nextAvailability(current) {
    return current === 'sold_out' ? 'available' : 'sold_out';
}

export function parseSchemaValue(value, type) {
    if (value === '') return null;
    const baseType = type.replace(/\?$/, '');
    if (baseType === 'integer') return Number.parseInt(value, 10);
    if (baseType === 'numeric') return Number(value);
    if (baseType.endsWith('[]')) return value.split(',').map((item) => item.trim()).filter(Boolean).map((item) => baseType === 'integer[]' ? Number.parseInt(item, 10) : item);
    return value;
}

export function buildReadyBlockTranslations(locales, values) {
    return Object.fromEntries(locales.map(({ locale }) => [locale, {
        content: values[locale] || {},
        translation_state: 'ready',
    }]));
}

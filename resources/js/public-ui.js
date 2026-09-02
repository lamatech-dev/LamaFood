export function normalizeSearchText(value, locale) {
    return String(value).trim().toLocaleLowerCase(locale);
}

export function productMatchesSearch(value, query, locale) {
    return normalizeSearchText(value, locale).includes(normalizeSearchText(query, locale));
}

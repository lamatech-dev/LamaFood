export function normalizeSearchText(value, locale) {
    return String(value).trim().toLocaleLowerCase(locale);
}

export function productMatchesSearch(value, query, locale) {
    return normalizeSearchText(value, locale).includes(normalizeSearchText(query, locale));
}

export function activeCategoryId(sections, boundary) {
    const passed = sections.filter((section) => section.top <= boundary);
    return passed.at(-1)?.id ?? sections[0]?.id ?? null;
}

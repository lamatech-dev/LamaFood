export function buildReadyTranslations(locales, values) {
    return Object.fromEntries(locales.map(({ locale }) => [locale, {
        ...values[locale],
        translation_state: 'ready',
    }]));
}

export function nextAvailability(current) {
    return current === 'sold_out' ? 'available' : 'sold_out';
}

export function requiresTableKey(type) {
    return type === 'table';
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

export function swapOrder(items, index, direction) {
    const target = index + direction;
    if (index < 0 || target < 0 || target >= items.length) return [...items];
    const reordered = [...items];
    [reordered[index], reordered[target]] = [reordered[target], reordered[index]];
    return reordered;
}

export function hasPermission(user, permission) {
    return user?.permissions?.includes(permission) === true;
}

export async function loadCollection(api, path) {
    const items = [];
    let page = 1;
    while (true) {
        const result = await api(`${path}${path.includes('?') ? '&' : '?'}page=${page}`);
        if (!Array.isArray(result?.data) || result.current_page !== page || !Number.isInteger(result.last_page) || result.last_page < page) {
            throw new Error('پاسخ فهرست معتبر نیست؛ دوباره تلاش کنید.');
        }
        items.push(...result.data);
        if (page >= result.last_page) return items;
        page += 1;
    }
}

export function collectionPage(items, requestedPage, pageSize = 20) {
    const pages = Math.max(1, Math.ceil(items.length / pageSize));
    const page = Math.min(pages, Math.max(1, requestedPage));
    return { items: items.slice((page - 1) * pageSize, page * pageSize), page, pages, total: items.length };
}

export function filterAdminCollection(items, { query = '', category = '', status = '', availability = '', branchId = null } = {}) {
    const normalize = (value) => String(value ?? '').normalize('NFKC').toLocaleLowerCase().replace(/ي/g, 'ی').replace(/ك/g, 'ک').trim();
    const needle = normalize(query);
    return items.filter((item) => {
        const searchable = [item.slug, item.public_id, ...(item.translations || []).flatMap((translation) => [translation.name, translation.title, translation.alt])];
        return (!needle || searchable.some((value) => normalize(value).includes(needle)))
            && (!category || item.category?.public_id === category)
            && (!status || item.publication_state === status)
            && (!availability || item.branch_settings?.some((setting) => setting.branch_id === branchId && setting.availability_state === availability));
    });
}

export function mediaLabel(item, locale) {
    const translation = item.translations?.find((entry) => entry.locale === locale);
    return translation?.title || translation?.alt || item.public_id;
}

export function adminErrorMessage(payload) {
    const fields = { slug: 'شناسه URL', name: 'نام', email: 'ایمیل', username: 'نام کاربری', price_amount: 'قیمت', file: 'فایل تصویر', category_id: 'دسته', role: 'نقش', password: 'رمز عبور', translations: 'ترجمه‌ها' };
    const rules = { required: 'الزامی است.', alpha_dash: 'فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.', unique: 'قبلاً استفاده شده است.', email: 'باید یک ایمیل معتبر باشد.', integer: 'باید عدد صحیح باشد.', min: 'از حداقل مجاز کمتر است.', max: 'از حداکثر مجاز بیشتر است.', in: 'مقدار مجاز ندارد.', exists: 'پیدا نشد.', image: 'باید تصویر معتبر باشد.', mimes: 'فرمت پشتیبانی‌شده ندارد.', confirmed: 'با تکرار آن مطابقت ندارد.' };
    const errors = Object.entries(payload.errors || {}).flatMap(([field, messages]) => messages.map((message) => {
        if (!message.startsWith('validation.')) return message;
        const parts = field.split('.');
        const label = fields[parts.at(-1)] || fields[parts[0]] || 'این مقدار';
        return `${label} ${rules[message.slice(11).split('.')[0]] || 'معتبر نیست؛ مقدار واردشده را بررسی کنید.'}`;
    }));
    return errors.join(' ') || payload.message || 'عملیات انجام نشد؛ دوباره تلاش کنید.';
}

export async function saveProductAccess(api, user, product, desired, setting) {
    if (hasPermission(user, 'menu.publish') && desired.publication_state !== product.publication_state) {
        await api(`/products/${product.public_id}/publication-state`, { method: 'PATCH', body: JSON.stringify({ publication_state: desired.publication_state }) });
    }
    const canPrice = hasPermission(user, 'menu.price');
    const canAvailability = hasPermission(user, 'menu.availability');
    if ((!canPrice && !canAvailability) || (!setting && !canPrice)) return;
    await api(`/products/${product.public_id}/branches/${desired.branch_id}/settings`, { method: 'PUT', body: JSON.stringify({
        price_amount: canPrice ? desired.price_amount : setting.price_amount,
        availability_state: canAvailability ? desired.availability_state : (setting?.availability_state ?? 'available'),
        expected_version: setting?.version ?? 0,
    }) });
}

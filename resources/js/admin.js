import '../css/admin.css';
import { buildReadyBlockTranslations, buildReadyTranslations, nextAvailability, parseSchemaValue, requiresTableKey, swapOrder } from './admin-data.js';

if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));

localStorage.removeItem('denardi_admin_token');

const cookieValue = (name) => document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith(`${name}=`))
    ?.split('=')
    .slice(1)
    .join('=');
const headers = () => {
    const requestHeaders = { Accept: 'application/json' };
    const csrfToken = cookieValue('XSRF-TOKEN');
    if (csrfToken) requestHeaders['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    return requestHeaders;
};
let csrfInitialized = false;

async function ensureCsrfCookie() {
    if (csrfInitialized) return;
    await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    csrfInitialized = true;
}

async function api(path, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) await ensureCsrfCookie();
    const requestHeaders = { ...headers(), ...(options.headers || {}) };
    if (options.body && !(options.body instanceof FormData)) requestHeaders['Content-Type'] = 'application/json';
    const response = await fetch(`/api/admin/v1${path}`, { ...options, credentials: 'same-origin', headers: requestHeaders });
    if (response.status === 401) {
        window.location.assign('/admin/login');
        throw new Error('نشست شما پایان یافته است.');
    }
    if (response.status === 419) {
        csrfInitialized = false;
        throw new Error('نشست امنیتی منقضی شده است. صفحه را تازه‌سازی کنید.');
    }
    if (response.status === 204) return null;
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'عملیات انجام نشد.');
    return payload.data;
}

async function passwordApi(path, body) {
    await ensureCsrfCookie();
    const response = await fetch(`/api/admin/v1/${path}`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { ...headers(), 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'عملیات انجام نشد.');
    return payload.data;
}

async function authenticatedFile(path) {
    const response = await fetch(`/api/admin/v1${path}`, { credentials: 'same-origin', headers: headers() });
    if (response.status === 401) {
        window.location.assign('/admin/login');
        throw new Error('نشست شما پایان یافته است.');
    }
    if (!response.ok) {
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.message || 'دریافت فایل انجام نشد.');
    }
    return response;
}

async function downloadArtwork(path) {
    const response = await authenticatedFile(path);
    const disposition = response.headers.get('Content-Disposition') || '';
    const filename = disposition.match(/filename="([^"]+)"/)?.[1] || 'qr-artwork';
    const href = URL.createObjectURL(await response.blob());
    const link = document.createElement('a');
    link.href = href;
    link.download = filename;
    document.body.append(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(href);
}

const loginForm = document.querySelector('[data-login-form]');
if (loginForm) {
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const error = document.querySelector('[data-form-error]');
        const data = new FormData(loginForm);
        try {
            await api('/login', { method: 'POST', body: JSON.stringify({ identifier: data.get('identifier'), password: data.get('password'), device_name: 'Denardi Admin Web' }) });
            window.location.assign('/admin');
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        }
    });
}

const forgotPasswordForm = document.querySelector('[data-forgot-password-form]');
forgotPasswordForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const error = forgotPasswordForm.querySelector('[data-form-error]');
    const success = forgotPasswordForm.querySelector('[data-form-success]');
    error.hidden = true;
    success.hidden = true;

    try {
        await passwordApi('forgot-password', { email: forgotPasswordForm.email.value });
        success.textContent = 'اگر حساب واجد شرایط باشد، لینک بازیابی ارسال شده است.';
        success.hidden = false;
        forgotPasswordForm.querySelector('button').disabled = true;
    } catch (exception) {
        error.textContent = exception.message;
        error.hidden = false;
    }
});

const resetPasswordForm = document.querySelector('[data-reset-password-form]');
resetPasswordForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const error = resetPasswordForm.querySelector('[data-form-error]');
    const success = resetPasswordForm.querySelector('[data-form-success]');
    error.hidden = true;
    success.hidden = true;

    try {
        await passwordApi('reset-password', {
            token: resetPasswordForm.token.value,
            email: resetPasswordForm.email.value,
            password: resetPasswordForm.password.value,
            password_confirmation: resetPasswordForm.password_confirmation.value,
        });
        success.textContent = 'رمز عبور تغییر کرد. اکنون می‌توانید وارد شوید.';
        success.hidden = false;
        resetPasswordForm.querySelector('button').disabled = true;
    } catch (exception) {
        error.textContent = exception.message;
        error.hidden = false;
    }
});

const dashboard = document.querySelector('[data-dashboard]');
if (dashboard) {
    const state = { context: null, categories: [], products: [], pages: [], media: [], qrCodes: [], backups: [], analytics: {}, blockSchemas: {}, me: null };
    const adminNavigation = document.querySelector('#admin-navigation');
    const adminNavigationToggle = document.querySelector('.admin-nav-toggle');
    adminNavigationToggle?.addEventListener('click', () => {
        const open = adminNavigationToggle.getAttribute('aria-expanded') !== 'true';
        adminNavigationToggle.setAttribute('aria-expanded', String(open));
        adminNavigation?.classList.toggle('open', open);
    });
    const localeValue = (translations, locale, field) => translations?.find((item) => item.locale === locale)?.[field] || '';
    const toast = (message) => {
        const element = document.querySelector('[data-toast]');
        element.textContent = message;
        element.hidden = false;
        window.setTimeout(() => { element.hidden = true; }, 3000);
    };
    const run = async (operation, success) => {
        try {
            await operation();
            if (success) toast(success);
        } catch (exception) { toast(exception.message); }
    };
    const actionButton = (label, handler, className = 'status') => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = label;
        button.addEventListener('click', handler);
        return button;
    };
    const translationInputs = (target, fields) => {
        target.replaceChildren();
        state.context.locales.forEach((metadata) => {
            const group = document.createElement('fieldset');
            group.dir = metadata.direction.value || metadata.direction;
            const legend = document.createElement('legend');
            legend.textContent = `${metadata.native_name} · ${metadata.locale.toUpperCase()}`;
            group.append(legend);
            fields.forEach(([field, label]) => {
                const wrapper = document.createElement('label');
                wrapper.textContent = label;
                const input = document.createElement(['description', 'ingredients', 'allergen_notice', 'meta_description'].includes(field) ? 'textarea' : 'input');
                input.dataset.locale = metadata.locale;
                input.dataset.field = field;
                if (field === 'name' || field === 'title') input.required = true;
                wrapper.append(input);
                group.append(wrapper);
            });
            target.append(group);
        });
    };
    const fillTranslations = (root, translations, contentField = null) => {
        root.querySelectorAll('[data-locale]').forEach((input) => {
            const translation = translations?.find((item) => item.locale === input.dataset.locale);
            const source = contentField ? translation?.[contentField] : translation;
            input.value = source?.[input.dataset.field] ?? '';
        });
    };
    const readTranslations = (root) => {
        const values = {};
        root.querySelectorAll('[data-locale]').forEach((input) => {
            values[input.dataset.locale] ||= {};
            values[input.dataset.locale][input.dataset.field] = input.value;
        });
        return buildReadyTranslations(state.context.locales, values);
    };
    const inputForSchema = (field, type, locale = null) => {
        const wrapper = document.createElement('label');
        wrapper.textContent = field;
        const isMediaField = field.toLowerCase().includes('mediaid');
        const input = document.createElement(isMediaField ? 'select' : (type.replace(/\?$/, '') === 'string' && ['body', 'caption', 'intro'].includes(field) ? 'textarea' : 'input'));
        input.dataset.field = field;
        input.dataset.schemaType = type;
        if (locale) input.dataset.locale = locale;
        if (!type.endsWith('?')) input.required = true;
        if (['integer', 'numeric'].includes(type.replace(/\?$/, ''))) input.type = 'number';
        if (isMediaField) {
            input.multiple = type.includes('[]');
            if (!input.multiple) input.append(new Option('بدون رسانه', ''));
            input.append(...state.media.map((item) => new Option(localeValue(item.translations, state.context.business.default_locale, 'title') || item.public_id, item.id)));
        } else if (type.includes('[]')) input.placeholder = 'با ویرگول جدا کنید';
        wrapper.append(input);
        return wrapper;
    };
    const renderBlockFields = (block = null) => {
        const form = document.querySelector('[data-block-form]');
        const schema = state.blockSchemas[form.type.value];
        const structureRoot = form.querySelector('[data-block-structure]');
        const translationsRoot = form.querySelector('[data-block-translations]');
        structureRoot.replaceChildren(...Object.entries(schema.structure).map(([field, type]) => inputForSchema(field, type)));
        translationsRoot.replaceChildren(...state.context.locales.map((metadata) => {
            const group = document.createElement('fieldset');
            group.dir = metadata.direction.value || metadata.direction;
            const legend = document.createElement('legend');
            legend.textContent = metadata.native_name;
            group.append(legend, ...Object.entries(schema.content).map(([field, type]) => inputForSchema(field, type, metadata.locale)));
            return group;
        }));
        if (block) {
            structureRoot.querySelectorAll('[data-field]').forEach((input) => {
                const value = block.structure_json?.[input.dataset.field];
                if (input.multiple) {
                    [...input.options].forEach((option) => { option.selected = (value || []).map(Number).includes(Number(option.value)); });
                } else input.value = Array.isArray(value) ? value.join(', ') : (value ?? '');
            });
            fillTranslations(translationsRoot, block.translations, 'content_json');
        }
    };
    const readSchemaFields = (root) => Object.fromEntries([...root.querySelectorAll('[data-schema-type]')]
        .map((input) => [input.dataset.field, input.multiple ? [...input.selectedOptions].map((option) => Number(option.value)) : parseSchemaValue(input.value, input.dataset.schemaType)])
        .filter(([, value]) => value !== null));
    const readBlockTranslations = (root) => {
        const values = {};
        root.querySelectorAll('[data-locale]').forEach((input) => {
            values[input.dataset.locale] ||= {};
            const value = parseSchemaValue(input.value, input.dataset.schemaType);
            if (value !== null) values[input.dataset.locale][input.dataset.field] = value;
        });
        return buildReadyBlockTranslations(state.context.locales, values);
    };
    const selectView = (view) => {
        document.querySelectorAll('[data-admin-view]').forEach((section) => { section.hidden = section.dataset.adminView !== view; });
        document.querySelectorAll('[data-admin-target]').forEach((button) => button.classList.toggle('active', button.dataset.adminTarget === view));
        const active = document.querySelector(`[data-admin-target="${view}"]`);
        document.querySelector('[data-view-title]').textContent = active?.childNodes[0]?.textContent.trim() || 'پنل مدیریت';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    const fillProductOptions = () => {
        const locale = state.context.business.default_locale;
        const form = document.querySelector('[data-product-form]');
        form.category_id.replaceChildren(...state.categories.map((category) => new Option(localeValue(category.translations, locale, 'name') || category.slug, category.public_id)));
        form.primary_media_id.replaceChildren(new Option('بدون تصویر', ''), ...state.media.map((item) => new Option(localeValue(item.translations, locale, 'title') || item.public_id, item.public_id)));
        form.branch_id.replaceChildren(...state.context.branches.map((branch) => new Option(branch.name, branch.id)));
    };
    const fillProductBranchSetting = (form, product) => {
        const setting = product?.branch_settings.find((item) => item.branch_id === Number(form.branch_id.value));
        form.price_amount.value = setting?.price_amount || '';
        form.availability_state.value = setting?.availability_state || 'available';
    };
    const resetCategoryForm = () => {
        const form = document.querySelector('[data-category-form]');
        form.reset();
        form.public_id.value = '';
    };
    const openProduct = (product = null) => {
        const form = document.querySelector('[data-product-form]');
        form.reset();
        fillProductOptions();
        document.querySelector('[data-product-dialog-title]').textContent = product ? 'ویرایش محصول' : 'محصول جدید';
        form.public_id.value = product?.public_id || '';
        form.slug.value = product?.slug || '';
        form.category_id.value = product?.category.public_id || state.categories[0]?.public_id || '';
        form.primary_media_id.value = product?.primary_media?.public_id || '';
        form.publication_state.value = product?.publication_state || 'published';
        form.is_featured.checked = product?.is_featured || false;
        form.is_new.checked = product?.is_new || false;
        form.is_best_seller.checked = product?.is_best_seller || false;
        form.branch_id.value = state.context.branches[0]?.id || '';
        fillProductBranchSetting(form, product);
        fillTranslations(form.querySelector('[data-product-translations]'), product?.translations);
        document.querySelector('[data-product-dialog]').showModal();
    };
    const openPage = (page = null) => {
        const form = document.querySelector('[data-page-form]');
        form.reset();
        document.querySelector('[data-page-dialog-title]').textContent = page ? 'ویرایش صفحه' : 'صفحه جدید';
        form.public_id.value = page?.public_id || '';
        form.slug.value = page?.slug || '';
        form.template.value = page?.template || 'standard';
        fillTranslations(form.querySelector('[data-page-translations]'), page?.translations);
        document.querySelector('[data-page-dialog]').showModal();
    };
    const openBlock = (page, block = null) => {
        const form = document.querySelector('[data-block-form]');
        form.reset();
        form.page_id.value = page.public_id;
        form.block_id.value = block?.public_id || '';
        form.type.value = block?.type || Object.keys(state.blockSchemas)[0];
        form.position.value = block?.position ?? page.blocks.length;
        form.is_enabled.checked = block?.is_enabled ?? true;
        form.querySelector('[data-position-field]').hidden = Boolean(block);
        document.querySelector('[data-block-dialog-title]').textContent = block ? 'ویرایش بلوک' : 'بلوک جدید';
        renderBlockFields(block);
        document.querySelector('[data-block-dialog]').showModal();
    };
    const openMedia = (item) => {
        const form = document.querySelector('[data-media-edit-form]');
        form.media_id.value = item.public_id;
        fillTranslations(form.querySelector('[data-media-edit-translations]'), item.translations);
        document.querySelector('[data-media-dialog]').showModal();
    };
    const reorderCategories = async (index, direction) => {
        const ids = swapOrder(state.categories.map((item) => item.public_id), index, direction);
        await api('/categories/order', { method: 'PUT', body: JSON.stringify({ categories: ids }) });
        state.categories = await api('/categories');
        render();
    };
    const reorderProducts = async (product, direction) => {
        const group = state.products.filter((item) => item.category_id === product.category_id);
        const index = group.findIndex((item) => item.public_id === product.public_id);
        await api('/products/order', { method: 'PUT', body: JSON.stringify({ category_id: product.category.public_id, products: swapOrder(group.map((item) => item.public_id), index, direction) }) });
        await loadProducts();
    };
    const reorderBlocks = async (page, index, direction) => {
        await api(`/cms/pages/${page.public_id}/blocks/order`, { method: 'PUT', body: JSON.stringify({ blocks: swapOrder(page.blocks.map((item) => item.public_id), index, direction) }) });
        await loadPages();
    };
    const previewPage = async (page, locale) => {
        const preview = window.open('about:blank', '_blank');
        if (preview) preview.opener = null;
        try {
            const response = await authenticatedFile(`/cms/pages/${page.public_id}/preview/${locale}`);
            const href = URL.createObjectURL(await response.blob());
            if (preview) preview.location.href = href;
            window.setTimeout(() => URL.revokeObjectURL(href), 60000);
        } catch (exception) {
            preview?.close();
            toast(exception.message);
        }
    };
    const render = () => {
        const locale = state.context.business.default_locale;
        document.querySelector('[data-product-count]').textContent = state.products.length;
        document.querySelector('[data-category-count]').textContent = state.categories.length;
        document.querySelector('[data-branch-count]').textContent = state.context.branches.length;
        document.querySelector('[data-qr-count]').textContent = state.qrCodes.filter((item) => item.is_active).length;
        document.querySelector('[data-scan-count]').textContent = state.analytics['30_days']?.scan || 0;
        document.querySelector('[data-scans-today]').textContent = state.analytics.today?.scan || 0;
        document.querySelector('[data-scans-week]').textContent = state.analytics['7_days']?.scan || 0;
        document.querySelector('[data-scans-month]').textContent = state.analytics['30_days']?.scan || 0;
        document.querySelector('[data-menu-views-month]').textContent = state.analytics['30_days']?.menu_view || 0;
        document.querySelector('[data-category-views-month]').textContent = state.analytics['30_days']?.category_view || 0;
        document.querySelector('[data-product-views-month]').textContent = state.analytics['30_days']?.product_view || 0;
        const deviceLabels = { mobile: 'موبایل', tablet: 'تبلت', desktop: 'دسکتاپ', bot: 'ربات', unknown: 'نامشخص' };
        document.querySelector('[data-device-breakdown]').replaceChildren(...(state.analytics.breakdowns?.devices_30_days || []).map((item) => {
            const card = document.createElement('div');
            card.append(Object.assign(document.createElement('span'), { textContent: deviceLabels[item.device_class] || item.device_class }), Object.assign(document.createElement('b'), { textContent: item.count }));
            return card;
        }));
        document.querySelector('[data-table-scan-breakdown]').replaceChildren(...(state.analytics.breakdowns?.table_scans_30_days || []).map((item) => {
            const row = document.createElement('article'); row.className = 'admin-row';
            row.append(Object.assign(document.createElement('strong'), { textContent: item.label }), Object.assign(document.createElement('small'), { textContent: item.table_key }), Object.assign(document.createElement('b'), { textContent: item.count }));
            return row;
        }));
        document.querySelector('[data-backup-status]').textContent = state.backups[0] ? `${state.backups[0].status} · ${state.backups[0].type}` : 'ثبت نشده';
        document.querySelector('[data-business-name]').textContent = state.context.business.name;
        document.querySelector('[data-user-name]').textContent = state.me.name;
        document.querySelector('[data-readiness-summary]').textContent = `${state.pages.filter((page) => page.readiness.ready).length} از ${state.pages.length} صفحه برای انتشار سه‌زبانه آماده است.`;

        const products = document.querySelector('[data-products]');
        products.replaceChildren(...state.products.map((product, index) => {
            const setting = product.branch_settings.find((item) => item.branch_id === state.context.branches[0]?.id);
            const row = document.createElement('article'); row.className = 'admin-row';
            const image = document.createElement('img'); image.className = 'row-thumbnail'; image.src = product.primary_media?.thumbnail_path ? `/storage/${product.primary_media.thumbnail_path}` : '/denardi-icon.svg'; image.alt = '';
            const copy = document.createElement('div'); copy.append(Object.assign(document.createElement('strong'), { textContent: localeValue(product.translations, locale, 'name') || product.slug }), Object.assign(document.createElement('small'), { textContent: `${product.publication_state} · ${product.slug}` }));
            const price = document.createElement('b'); price.textContent = setting ? `${Number(setting.price_amount).toLocaleString('fa-IR')} ریال · ${setting.availability_state}` : 'بدون قیمت';
            const actions = document.createElement('div'); actions.className = 'row-actions';
            actions.append(actionButton('ویرایش', () => openProduct(product)), actionButton('↑', () => run(() => reorderProducts(product, -1))), actionButton('↓', () => run(() => reorderProducts(product, 1))), actionButton('حذف', () => { if (window.confirm('محصول حذف یا آرشیو شود؟')) run(async () => { await api(`/products/${product.public_id}`, { method: 'DELETE' }); await loadProducts(); }, 'وضعیت محصول اعمال شد.'); }, 'status danger'));
            const availability = actionButton(setting?.availability_state === 'sold_out' ? 'ناموجود' : 'موجود', () => run(async () => { await api(`/products/${product.public_id}/branches/${setting.branch_id}/settings`, { method: 'PUT', body: JSON.stringify({ price_amount: setting.price_amount, availability_state: nextAvailability(setting.availability_state), expected_version: setting.version }) }); await loadProducts(); }, 'موجودی ذخیره شد.'), setting?.availability_state === 'sold_out' ? 'status sold' : 'status'); availability.disabled = !setting; actions.append(availability);
            row.append(image, copy, price, actions); return row;
        }));

        const parentSelect = document.querySelector('[data-category-form] select[name="parent_id"]');
        parentSelect.replaceChildren(new Option('بدون والد', ''), ...state.categories.map((category) => new Option(localeValue(category.translations, locale, 'name') || category.slug, category.public_id)));
        const categories = document.querySelector('[data-categories]');
        categories.replaceChildren(...state.categories.map((category, index) => {
            const row = document.createElement('article'); row.className = 'admin-row';
            const copy = document.createElement('div'); copy.append(Object.assign(document.createElement('strong'), { textContent: localeValue(category.translations, locale, 'name') || category.slug }), Object.assign(document.createElement('small'), { textContent: `${category.publication_state} · ${category.products_count} محصول · ${category.parent ? 'زیرمجموعه' : 'اصلی'}` }));
            const actions = document.createElement('div'); actions.className = 'row-actions';
            actions.append(actionButton('ویرایش', () => { const form = document.querySelector('[data-category-form]'); form.public_id.value = category.public_id; form.slug.value = category.slug; form.parent_id.value = category.parent?.public_id || ''; form.is_featured.checked = category.is_featured; fillTranslations(form.querySelector('[data-category-translations]'), category.translations); window.scrollTo({ top: 0, behavior: 'smooth' }); }), actionButton('↑', () => run(() => reorderCategories(index, -1))), actionButton('↓', () => run(() => reorderCategories(index, 1))), actionButton('حذف', () => { if (window.confirm('دسته حذف یا آرشیو شود؟')) run(async () => { await api(`/categories/${category.public_id}`, { method: 'DELETE' }); state.categories = await api('/categories'); render(); }, 'وضعیت دسته اعمال شد.'); }, 'status danger'));
            row.append(copy, actions); return row;
        }));

        const pages = document.querySelector('[data-pages]'); pages.replaceChildren();
        state.pages.forEach((page) => {
            const wrapper = document.createElement('article'); wrapper.className = 'page-card';
            const row = document.createElement('div'); row.className = 'admin-row';
            const copy = document.createElement('div'); copy.append(Object.assign(document.createElement('strong'), { textContent: localeValue(page.translations, locale, 'title') || page.slug }), Object.assign(document.createElement('small'), { textContent: `${page.status} · ${page.blocks.length} بلوک · ${page.has_unpublished_changes ? 'تغییر منتشرنشده' : 'همگام با انتشار'}` }));
            const readiness = document.createElement('b'); readiness.textContent = page.readiness.ready ? 'سه‌زبانه آماده' : Object.entries(page.readiness.locales).filter(([, value]) => !value.ready).map(([code]) => code.toUpperCase()).join('، ') + ' ناقص';
            const actions = document.createElement('div'); actions.className = 'row-actions';
            actions.append(actionButton('ویرایش', () => openPage(page)), actionButton('بلوک جدید', () => openBlock(page)), ...state.context.locales.map((item) => actionButton(`پیش‌نمایش ${item.locale.toUpperCase()}`, () => previewPage(page, item.locale))));
            const publish = actionButton('انتشار', () => run(async () => { await api(`/cms/pages/${page.public_id}/publish`, { method: 'POST', body: JSON.stringify({ expected_revision: page.revision }) }); await loadPages(); }, 'نسخه سه‌زبانه منتشر شد.')); publish.disabled = !page.readiness.ready; actions.append(publish);
            if (page.slug !== 'home') actions.append(actionButton('حذف', () => { if (window.confirm('صفحه حذف یا آرشیو شود؟')) run(async () => { await api(`/cms/pages/${page.public_id}`, { method: 'DELETE' }); await loadPages(); }, 'وضعیت صفحه اعمال شد.'); }, 'status danger'));
            row.append(copy, readiness, actions); wrapper.append(row);
            const blockList = document.createElement('div'); blockList.className = 'block-list';
            page.blocks.forEach((block, index) => {
                const item = document.createElement('div'); item.className = 'block-row';
                const label = document.createElement('span'); label.textContent = `${index + 1}. ${block.type} · ${block.is_enabled ? 'فعال' : 'غیرفعال'}`;
                const controls = document.createElement('div'); controls.className = 'row-actions';
                controls.append(actionButton('ویرایش', () => openBlock(page, block)), actionButton(block.is_enabled ? 'غیرفعال' : 'فعال', () => run(async () => { await api(`/cms/pages/${page.public_id}/blocks/${block.public_id}`, { method: 'PUT', body: JSON.stringify({ type: block.type, is_enabled: !block.is_enabled, structure: block.structure_json, translations: Object.fromEntries(block.translations.map((translation) => [translation.locale, { content: translation.content_json, translation_state: translation.translation_state }])) }) }); await loadPages(); }, 'وضعیت بلوک ذخیره شد.')), actionButton('↑', () => run(() => reorderBlocks(page, index, -1))), actionButton('↓', () => run(() => reorderBlocks(page, index, 1))), actionButton('حذف', () => { if (window.confirm('بلوک حذف شود؟')) run(async () => { await api(`/cms/pages/${page.public_id}/blocks/${block.public_id}`, { method: 'DELETE' }); await loadPages(); }, 'بلوک حذف شد.'); }, 'status danger'));
                item.append(label, controls); blockList.append(item);
            });
            wrapper.append(blockList); pages.append(wrapper);
        });

        const media = document.querySelector('[data-media]');
        media.replaceChildren(...state.media.map((item) => {
            const card = document.createElement('article'); card.className = 'media-card';
            const image = document.createElement('img'); image.src = `/storage/${item.thumbnail_path || item.path}`; image.alt = localeValue(item.translations, locale, 'alt');
            const copy = document.createElement('div'); copy.append(Object.assign(document.createElement('strong'), { textContent: localeValue(item.translations, locale, 'title') || item.public_id }), Object.assign(document.createElement('small'), { textContent: `${item.width || '—'}×${item.height || '—'} · ${item.usages_count + item.products_count} استفاده` }));
            const references = [...item.usages.map((usage) => usage.field), ...item.products.map((product) => `محصول: ${product.slug}`)];
            if (references.length) copy.append(Object.assign(document.createElement('small'), { textContent: references.join(' · ') }));
            const actions = document.createElement('div'); actions.className = 'row-actions'; actions.append(actionButton('ویرایش', () => openMedia(item)), actionButton('حذف', () => { if (window.confirm('رسانه حذف شود؟')) run(async () => { await api(`/media/${item.public_id}`, { method: 'DELETE' }); await loadMedia(); }, 'رسانه حذف شد.'); }, 'status danger')); copy.append(actions); card.append(image, copy); return card;
        }));

        const qrList = document.querySelector('[data-qr-codes]');
        qrList.replaceChildren(...state.qrCodes.map((qrCode) => {
            const row = document.createElement('article'); row.className = 'admin-row';
            const copy = document.createElement('div'); copy.append(Object.assign(document.createElement('strong'), { textContent: qrCode.label }), Object.assign(document.createElement('small'), { textContent: `${qrCode.type === 'table' ? `میز ${qrCode.table_key}` : 'منوی عمومی'} · ${qrCode.branch.name}` }));
            const path = document.createElement('b'); path.className = 'qr-path'; path.textContent = `/q/${qrCode.public_id}`;
            const actions = document.createElement('div'); actions.className = 'row-actions';
            ['svg', 'png', 'pdf'].forEach((format) => actions.append(actionButton(format.toUpperCase(), () => run(() => downloadArtwork(`/qr-codes/${qrCode.public_id}/artwork/${format}`), `فایل ${format.toUpperCase()} آماده شد.`))));
            actions.append(actionButton(qrCode.is_active ? 'فعال' : 'غیرفعال', () => run(async () => { await api(`/qr-codes/${qrCode.public_id}`, { method: 'PATCH', body: JSON.stringify({ is_active: !qrCode.is_active }) }); await loadQrAnalytics(); }, 'وضعیت QR ذخیره شد.'), qrCode.is_active ? 'status' : 'status sold'));
            row.append(copy, path, actions); return row;
        }));

        const localeSettings = document.querySelector('[data-locale-settings]');
        localeSettings.replaceChildren(...state.context.locales.map((item) => { const card = document.createElement('article'); card.innerHTML = `<strong>${item.native_name}</strong><span>${item.locale.toUpperCase()}</span><small>${item.direction.value || item.direction}${item.locale === state.context.business.default_locale ? ' · پیش‌فرض' : ''}</small>`; return card; }));
        fillProductOptions();
    };

    async function loadProducts() { const data = await api('/products'); state.products = data.data; render(); }
    async function loadPages() { state.pages = await api('/cms/pages'); render(); }
    async function loadMedia() { const data = await api('/media'); state.media = data.data; render(); }
    async function loadQrAnalytics() { [state.qrCodes, state.analytics] = await Promise.all([api('/qr-codes'), api('/analytics/summary')]); render(); }
    async function boot() {
        try {
            [state.me, state.context, state.categories, state.pages, state.blockSchemas] = await Promise.all([api('/me'), api('/business/context'), api('/categories'), api('/cms/pages'), api('/cms/block-schemas')]);
            const [products, media, qrCodes, analytics, backups] = await Promise.all([api('/products'), api('/media'), api('/qr-codes'), api('/analytics/summary'), api('/backups').catch(() => [])]);
            state.products = products.data; state.media = media.data; state.qrCodes = qrCodes; state.analytics = analytics; state.backups = backups;
            translationInputs(document.querySelector('[data-category-translations]'), [['name', 'نام دسته'], ['description', 'توضیح']]);
            translationInputs(document.querySelector('[data-product-translations]'), [['name', 'نام محصول'], ['description', 'توضیح'], ['ingredients', 'ترکیبات'], ['allergen_notice', 'هشدار حساسیت']]);
            translationInputs(document.querySelector('[data-page-translations]'), [['title', 'عنوان'], ['meta_title', 'عنوان SEO'], ['meta_description', 'توضیح SEO']]);
            translationInputs(document.querySelector('[data-media-translations]'), [['alt', 'متن جایگزین'], ['title', 'عنوان']]);
            translationInputs(document.querySelector('[data-media-edit-translations]'), [['alt', 'متن جایگزین'], ['title', 'عنوان']]);
            const typeSelect = document.querySelector('[data-block-form] select[name="type"]');
            typeSelect.replaceChildren(...Object.keys(state.blockSchemas).map((type) => new Option(type, type)));
            typeSelect.addEventListener('change', () => renderBlockFields());
            const qrBranch = document.querySelector('[data-qr-form] select[name="branch_id"]');
            qrBranch.replaceChildren(...state.context.branches.map((branch) => new Option(branch.name, branch.id)));
            render(); document.querySelector('[data-loading]').hidden = true; dashboard.hidden = false;
        } catch (exception) {
            const loading = document.querySelector('[data-loading]');
            loading.firstChild.textContent = `${exception.message} `;
            loading.querySelector('[data-retry]').hidden = false;
        }
    }

    document.querySelectorAll('[data-admin-target]').forEach((button) => button.addEventListener('click', () => {
        selectView(button.dataset.adminTarget);
        adminNavigation?.classList.remove('open');
        adminNavigationToggle?.setAttribute('aria-expanded', 'false');
    }));
    document.querySelector('[data-retry]')?.addEventListener('click', () => window.location.reload());
    document.querySelector('[data-open-product]').addEventListener('click', () => openProduct());
    document.querySelector('[data-product-form] select[name="branch_id"]').addEventListener('change', (event) => {
        const form = event.currentTarget.form;
        fillProductBranchSetting(form, state.products.find((item) => item.public_id === form.public_id.value));
    });
    document.querySelector('[data-open-page]').addEventListener('click', () => openPage());
    document.querySelector('[data-reset-category]').addEventListener('click', resetCategoryForm);
    document.querySelector('[data-logout]').addEventListener('click', async () => { try { await api('/logout', { method: 'POST' }); } finally { window.location.assign('/admin/login'); } });

    document.querySelector('[data-category-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget; const id = form.public_id.value;
        const existing = state.categories.find((item) => item.public_id === id);
        const category = await api(id ? `/categories/${id}` : '/categories', { method: id ? 'PUT' : 'POST', body: JSON.stringify({ slug: form.slug.value, parent_id: form.parent_id.value || null, position: existing?.position || state.categories.length, is_featured: form.is_featured.checked, translations: readTranslations(form) }) });
        if (!id) await api(`/categories/${category.public_id}/publication-state`, { method: 'PATCH', body: JSON.stringify({ publication_state: 'published' }) });
        state.categories = await api('/categories'); resetCategoryForm(); render();
    }, 'دسته ذخیره شد.'));

    document.querySelector('[data-product-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget; const id = form.public_id.value;
        const existing = state.products.find((item) => item.public_id === id); const branchId = Number(form.branch_id.value); const setting = existing?.branch_settings.find((item) => item.branch_id === branchId);
        const product = await api(id ? `/products/${id}` : '/products', { method: id ? 'PUT' : 'POST', body: JSON.stringify({ category_id: form.category_id.value, primary_media_id: form.primary_media_id.value || null, slug: form.slug.value, position: existing?.position ?? state.products.filter((item) => item.category.public_id === form.category_id.value).length, translations: readTranslations(form), is_featured: form.is_featured.checked, is_new: form.is_new.checked, is_best_seller: form.is_best_seller.checked }) });
        if (form.publication_state.value !== product.publication_state) await api(`/products/${product.public_id}/publication-state`, { method: 'PATCH', body: JSON.stringify({ publication_state: form.publication_state.value }) });
        await api(`/products/${product.public_id}/branches/${branchId}/settings`, { method: 'PUT', body: JSON.stringify({ price_amount: Number(form.price_amount.value), availability_state: form.availability_state.value, expected_version: setting?.version || 0 }) });
        document.querySelector('[data-product-dialog]').close(); await loadProducts();
    }, 'محصول، قیمت و موجودی ذخیره شد.'));

    document.querySelector('[data-page-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget; const id = form.public_id.value;
        await api(id ? `/cms/pages/${id}` : '/cms/pages', { method: id ? 'PUT' : 'POST', body: JSON.stringify({ slug: form.slug.value, template: form.template.value, translations: readTranslations(form) }) });
        document.querySelector('[data-page-dialog]').close(); await loadPages();
    }, 'صفحه ذخیره شد.'));

    document.querySelector('[data-block-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget; const id = form.block_id.value;
        const payload = { type: form.type.value, is_enabled: form.is_enabled.checked, structure: readSchemaFields(form.querySelector('[data-block-structure]')), translations: readBlockTranslations(form.querySelector('[data-block-translations]')) };
        if (!id) payload.position = Number(form.position.value);
        await api(`/cms/pages/${form.page_id.value}/blocks${id ? `/${id}` : ''}`, { method: id ? 'PUT' : 'POST', body: JSON.stringify(payload) });
        document.querySelector('[data-block-dialog]').close(); await loadPages();
    }, 'بلوک ذخیره شد.'));

    document.querySelector('[data-media-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget; const payload = new FormData(); payload.append('file', form.file.files[0]);
        Object.entries(readTranslations(form)).forEach(([locale, fields]) => Object.entries(fields).forEach(([field, value]) => { if (field !== 'translation_state') payload.append(`translations[${locale}][${field}]`, value); }));
        await api('/media', { method: 'POST', body: payload }); form.reset(); await loadMedia();
    }, 'رسانه و مشتقات WebP ذخیره شدند.'));

    document.querySelector('[data-media-edit-form]').addEventListener('submit', (event) => run(async () => {
        event.preventDefault(); const form = event.currentTarget;
        await api(`/media/${form.media_id.value}`, { method: 'PATCH', body: JSON.stringify({ translations: readTranslations(form) }) });
        document.querySelector('[data-media-dialog]').close(); await loadMedia();
    }, 'اطلاعات رسانه ذخیره شد.'));

    const qrForm = document.querySelector('[data-qr-form]');
    qrForm.type.addEventListener('change', () => { const field = qrForm.querySelector('[data-table-key]'); field.hidden = !requiresTableKey(qrForm.type.value); qrForm.table_key.required = requiresTableKey(qrForm.type.value); if (!requiresTableKey(qrForm.type.value)) qrForm.table_key.value = ''; });
    qrForm.addEventListener('submit', (event) => run(async () => { event.preventDefault(); await api('/qr-codes', { method: 'POST', body: JSON.stringify({ branch_id: Number(qrForm.branch_id.value), type: qrForm.type.value, label: qrForm.label.value, table_key: requiresTableKey(qrForm.type.value) ? qrForm.table_key.value : null }) }); qrForm.reset(); await loadQrAnalytics(); }, 'QR ساخته شد.'));
    boot();
}

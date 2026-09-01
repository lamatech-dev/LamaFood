import '../css/admin.css';
import { buildReadyBlockTranslations, buildReadyTranslations, nextAvailability, parseSchemaValue, requiresTableKey } from './admin-data.js';

const tokenKey = 'denardi_admin_token';
const token = localStorage.getItem(tokenKey);
const headers = () => ({ Accept: 'application/json', Authorization: `Bearer ${localStorage.getItem(tokenKey) || ''}` });

async function api(path, options = {}) {
    const requestHeaders = { ...headers(), ...(options.headers || {}) };
    if (options.body && !(options.body instanceof FormData)) requestHeaders['Content-Type'] = 'application/json';
    const response = await fetch(`/api/admin/v1${path}`, { ...options, headers: requestHeaders });
    if (response.status === 401) {
        localStorage.removeItem(tokenKey);
        window.location.assign('/admin/login');
        throw new Error('نشست شما پایان یافته است.');
    }
    if (response.status === 204) return null;
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'عملیات انجام نشد.');
    return payload.data;
}

const loginForm = document.querySelector('[data-login-form]');
if (loginForm) {
    if (token) window.location.assign('/admin');
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const error = document.querySelector('[data-form-error]');
        const data = new FormData(loginForm);
        try {
            const result = await api('/login', { method: 'POST', body: JSON.stringify({ identifier: data.get('identifier'), password: data.get('password'), device_name: 'Denardi Admin Web' }) });
            localStorage.setItem(tokenKey, result.token);
            window.location.assign('/admin');
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        }
    });
}

const dashboard = document.querySelector('[data-dashboard]');
if (dashboard) {
    if (!token) window.location.assign('/admin/login');

    const state = { context: null, categories: [], products: [], pages: [], media: [], qrCodes: [], analytics: {}, blockSchemas: {}, me: null };
    const toast = (message) => {
        const element = document.querySelector('[data-toast]');
        element.textContent = message;
        element.hidden = false;
        window.setTimeout(() => { element.hidden = true; }, 2800);
    };
    const translationInputs = (target, fields) => {
        target.replaceChildren();
        state.context.locales.forEach((metadata) => {
            const group = document.createElement('fieldset');
            group.dir = metadata.direction.value || metadata.direction;
            const legend = document.createElement('legend');
            legend.textContent = metadata.native_name;
            group.append(legend);
            fields.forEach(([field, label]) => {
                const wrapper = document.createElement('label');
                wrapper.textContent = label;
                const input = document.createElement(field === 'description' ? 'textarea' : 'input');
                input.dataset.locale = metadata.locale;
                input.dataset.field = field;
                if (field === 'name') input.required = true;
                wrapper.append(input);
                group.append(wrapper);
            });
            target.append(group);
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
        const input = document.createElement(type.replace(/\?$/, '') === 'string' && ['body', 'caption', 'intro'].includes(field) ? 'textarea' : 'input');
        input.dataset.field = field;
        input.dataset.schemaType = type;
        if (locale) input.dataset.locale = locale;
        if (!type.endsWith('?')) input.required = true;
        if (type.replace(/\?$/, '') === 'integer' || type.replace(/\?$/, '') === 'numeric') input.type = 'number';
        if (type.includes('[]')) input.placeholder = 'با ویرگول جدا کنید';
        wrapper.append(input);
        return wrapper;
    };
    const renderBlockFields = () => {
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
    };
    const readSchemaFields = (root) => Object.fromEntries([...root.querySelectorAll('[data-schema-type]')]
        .map((input) => [input.dataset.field, parseSchemaValue(input.value, input.dataset.schemaType)])
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
    const render = () => {
        document.querySelector('[data-product-count]').textContent = state.products.length;
        document.querySelector('[data-category-count]').textContent = state.categories.length;
        document.querySelector('[data-branch-count]').textContent = state.context.branches.length;
        document.querySelector('[data-qr-count]').textContent = state.qrCodes.filter((qrCode) => qrCode.is_active).length;
        document.querySelector('[data-scan-count]').textContent = state.analytics['30_days']?.scan || 0;
        document.querySelector('[data-scans-today]').textContent = state.analytics.today?.scan || 0;
        document.querySelector('[data-scans-week]').textContent = state.analytics['7_days']?.scan || 0;
        document.querySelector('[data-scans-month]').textContent = state.analytics['30_days']?.scan || 0;
        document.querySelector('[data-business-name]').textContent = state.context.business.name;
        document.querySelector('[data-user-name]').textContent = state.me.name;
        const locale = state.context.business.default_locale;
        const list = document.querySelector('[data-products]');
        list.replaceChildren();
        state.products.forEach((product) => {
            const translation = product.translations.find((item) => item.locale === locale);
            const setting = product.branch_settings.find((item) => item.branch_id === state.context.branches[0]?.id);
            const row = document.createElement('article');
            row.className = 'admin-row';
            const copy = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = translation?.name || product.slug;
            const meta = document.createElement('small');
            meta.textContent = `${product.publication_state} · ${product.slug}`;
            copy.append(name, meta);
            const price = document.createElement('b');
            price.textContent = setting ? `${Number(setting.price_amount).toLocaleString('fa-IR')} ریال` : 'بدون قیمت';
            const availability = document.createElement('button');
            availability.className = setting?.availability_state === 'sold_out' ? 'status sold' : 'status';
            availability.textContent = setting?.availability_state === 'sold_out' ? 'ناموجود' : 'موجود';
            availability.disabled = !setting;
            availability.addEventListener('click', async () => {
                try {
                    await api(`/products/${product.public_id}/branches/${setting.branch_id}/settings`, { method: 'PUT', body: JSON.stringify({ price_amount: setting.price_amount, availability_state: nextAvailability(setting.availability_state), expected_version: setting.version }) });
                    await loadProducts();
                    toast('وضعیت موجودی ذخیره شد.');
                } catch (exception) { toast(exception.message); }
            });
            row.append(copy, price, availability);
            list.append(row);
        });
        const categorySelect = document.querySelector('[data-product-form] select[name="category_id"]');
        categorySelect.replaceChildren(...state.categories.map((category) => {
            const option = document.createElement('option');
            option.value = category.public_id;
            option.textContent = category.translations.find((item) => item.locale === locale)?.name || category.slug;
            return option;
        }));
        const pagesList = document.querySelector('[data-pages]');
        pagesList.replaceChildren();
        state.pages.forEach((page) => {
            const row = document.createElement('article');
            row.className = 'admin-row';
            const copy = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = page.translations.find((item) => item.locale === locale)?.title || page.slug;
            const meta = document.createElement('small');
            meta.textContent = `${page.status} · ${page.blocks.length} بلوک · ویرایش ${page.revision}`;
            copy.append(title, meta);
            const readiness = document.createElement('b');
            readiness.textContent = page.readiness.ready ? 'سه‌زبانه آماده' : 'ترجمه ناقص';
            const actions = document.createElement('div');
            actions.className = 'row-actions';
            const blockButton = document.createElement('button');
            blockButton.className = 'status';
            blockButton.textContent = 'بلوک جدید';
            blockButton.addEventListener('click', () => {
                const form = document.querySelector('[data-block-form]');
                form.page_id.value = page.public_id;
                form.position.value = page.blocks.length;
                renderBlockFields();
                document.querySelector('[data-block-dialog]').showModal();
            });
            const publishButton = document.createElement('button');
            publishButton.className = 'status';
            publishButton.textContent = 'انتشار';
            publishButton.disabled = !page.readiness.ready;
            publishButton.addEventListener('click', async () => {
                try {
                    await api(`/cms/pages/${page.public_id}/publish`, { method: 'POST', body: JSON.stringify({ expected_revision: page.revision }) });
                    await loadPages();
                    toast('نسخهٔ سه‌زبانه صفحه منتشر شد.');
                } catch (exception) { toast(exception.message); }
            });
            actions.append(blockButton, publishButton);
            row.append(copy, readiness, actions);
            pagesList.append(row);
        });
        const mediaList = document.querySelector('[data-media]');
        mediaList.replaceChildren(...state.media.map((item) => {
            const card = document.createElement('article');
            card.className = 'media-card';
            const image = document.createElement('img');
            image.src = `/storage/${item.path}`;
            image.alt = item.translations.find((translation) => translation.locale === locale)?.alt || '';
            const copy = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = item.translations.find((translation) => translation.locale === locale)?.title || item.public_id;
            const details = document.createElement('small');
            details.textContent = `${item.width || '—'}×${item.height || '—'}`;
            copy.append(name, details);
            card.append(image, copy);
            return card;
        }));
        const qrList = document.querySelector('[data-qr-codes]');
        qrList.replaceChildren(...state.qrCodes.map((qrCode) => {
            const row = document.createElement('article');
            row.className = 'admin-row';
            const copy = document.createElement('div');
            const label = document.createElement('strong');
            label.textContent = qrCode.label;
            const meta = document.createElement('small');
            meta.textContent = `${qrCode.type === 'table' ? `میز ${qrCode.table_key}` : 'منوی عمومی'} · ${qrCode.branch.name}`;
            copy.append(label, meta);
            const path = document.createElement('b');
            path.className = 'qr-path';
            path.textContent = `/q/${qrCode.public_id}`;
            const toggle = document.createElement('button');
            toggle.className = qrCode.is_active ? 'status' : 'status sold';
            toggle.textContent = qrCode.is_active ? 'فعال' : 'غیرفعال';
            toggle.addEventListener('click', async () => {
                try {
                    await api(`/qr-codes/${qrCode.public_id}`, { method: 'PATCH', body: JSON.stringify({ is_active: !qrCode.is_active }) });
                    await loadQrAnalytics();
                    toast('وضعیت QR ذخیره شد.');
                } catch (exception) { toast(exception.message); }
            });
            row.append(copy, path, toggle);
            return row;
        }));
    };
    async function loadProducts() {
        const products = await api('/products');
        state.products = products.data;
        render();
    }
    async function loadPages() {
        state.pages = await api('/cms/pages');
        render();
    }
    async function loadMedia() {
        const media = await api('/media');
        state.media = media.data;
        render();
    }
    async function loadQrAnalytics() {
        [state.qrCodes, state.analytics] = await Promise.all([api('/qr-codes'), api('/analytics/summary')]);
        render();
    }
    async function boot() {
        try {
            [state.me, state.context, state.categories, state.pages, state.blockSchemas] = await Promise.all([api('/me'), api('/business/context'), api('/categories'), api('/cms/pages'), api('/cms/block-schemas')]);
            const [products, media, qrCodes, analytics] = await Promise.all([api('/products'), api('/media'), api('/qr-codes'), api('/analytics/summary')]);
            state.products = products.data;
            state.media = media.data;
            state.qrCodes = qrCodes;
            state.analytics = analytics;
            translationInputs(document.querySelector('[data-category-translations]'), [['name', 'نام دسته']]);
            translationInputs(document.querySelector('[data-product-translations]'), [['name', 'نام محصول'], ['description', 'توضیح']]);
            translationInputs(document.querySelector('[data-page-translations]'), [['title', 'عنوان'], ['meta_title', 'عنوان SEO'], ['meta_description', 'توضیح SEO']]);
            translationInputs(document.querySelector('[data-media-translations]'), [['alt', 'متن جایگزین'], ['title', 'عنوان']]);
            const typeSelect = document.querySelector('[data-block-form] select[name="type"]');
            typeSelect.replaceChildren(...Object.keys(state.blockSchemas).map((type) => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                return option;
            }));
            typeSelect.addEventListener('change', renderBlockFields);
            const qrBranch = document.querySelector('[data-qr-form] select[name="branch_id"]');
            qrBranch.replaceChildren(...state.context.branches.map((branch) => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = branch.name;
                return option;
            }));
            render();
            document.querySelector('[data-loading]').hidden = true;
            dashboard.hidden = false;
        } catch (exception) {
            document.querySelector('[data-loading]').textContent = exception.message;
        }
    }
    document.querySelector('[data-open-product]').addEventListener('click', () => document.querySelector('[data-product-dialog]').showModal());
    document.querySelector('[data-open-page]').addEventListener('click', () => document.querySelector('[data-page-dialog]').showModal());
    document.querySelectorAll('[data-section-link]').forEach((link) => link.addEventListener('click', () => document.getElementById(link.dataset.sectionLink)?.scrollIntoView({ behavior: 'smooth' })));
    document.querySelector('[data-logout]').addEventListener('click', async () => {
        try { await api('/logout', { method: 'POST' }); } finally { localStorage.removeItem(tokenKey); window.location.assign('/admin/login'); }
    });
    document.querySelector('[data-category-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        try {
            const category = await api('/categories', { method: 'POST', body: JSON.stringify({ slug: form.slug.value, translations: readTranslations(form) }) });
            await api(`/categories/${category.public_id}/publication-state`, { method: 'PATCH', body: JSON.stringify({ publication_state: 'published' }) });
            state.categories = await api('/categories');
            form.reset();
            render();
            toast('دسته ساخته و منتشر شد.');
        } catch (exception) { toast(exception.message); }
    });
    document.querySelector('[data-product-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const error = form.querySelector('[data-product-error]');
        try {
            const product = await api('/products', { method: 'POST', body: JSON.stringify({ category_id: form.category_id.value, slug: form.slug.value, translations: readTranslations(form), is_new: form.is_new.checked, is_best_seller: form.is_best_seller.checked }) });
            await api(`/products/${product.public_id}/publication-state`, { method: 'PATCH', body: JSON.stringify({ publication_state: 'published' }) });
            await api(`/products/${product.public_id}/branches/${state.context.branches[0].id}/settings`, { method: 'PUT', body: JSON.stringify({ price_amount: Number(form.price_amount.value), availability_state: 'available', expected_version: 0 }) });
            form.reset();
            document.querySelector('[data-product-dialog]').close();
            await loadProducts();
            toast('محصول ساخته، قیمت‌گذاری و منتشر شد.');
        } catch (exception) { error.textContent = exception.message; error.hidden = false; }
    });
    document.querySelector('[data-page-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const error = form.querySelector('[data-page-error]');
        try {
            await api('/cms/pages', { method: 'POST', body: JSON.stringify({ slug: form.slug.value, template: form.template.value, translations: readTranslations(form) }) });
            form.reset();
            document.querySelector('[data-page-dialog]').close();
            await loadPages();
            toast('صفحهٔ سه‌زبانه ساخته شد؛ بلوک‌ها را اضافه کنید.');
        } catch (exception) { error.textContent = exception.message; error.hidden = false; }
    });
    document.querySelector('[data-block-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const error = form.querySelector('[data-block-error]');
        try {
            await api(`/cms/pages/${form.page_id.value}/blocks`, { method: 'POST', body: JSON.stringify({
                type: form.type.value,
                position: Number(form.position.value),
                structure: readSchemaFields(form.querySelector('[data-block-structure]')),
                translations: readBlockTranslations(form.querySelector('[data-block-translations]')),
            }) });
            form.reset();
            document.querySelector('[data-block-dialog]').close();
            await loadPages();
            toast('بلوک با محتوای مستقل FA/EN/AR اضافه شد.');
        } catch (exception) { error.textContent = exception.message; error.hidden = false; }
    });
    document.querySelector('[data-media-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const payload = new FormData();
        payload.append('file', form.file.files[0]);
        const translations = readTranslations(form);
        Object.entries(translations).forEach(([locale, fields]) => Object.entries(fields).forEach(([field, value]) => {
            if (field !== 'translation_state') payload.append(`translations[${locale}][${field}]`, value);
        }));
        try {
            await api('/media', { method: 'POST', body: payload });
            form.reset();
            await loadMedia();
            toast('تصویر و اطلاعات سه‌زبانه بارگذاری شد.');
        } catch (exception) { toast(exception.message); }
    });
    const qrForm = document.querySelector('[data-qr-form]');
    qrForm.type.addEventListener('change', () => {
        const tableField = qrForm.querySelector('[data-table-key]');
        tableField.hidden = !requiresTableKey(qrForm.type.value);
        qrForm.table_key.required = requiresTableKey(qrForm.type.value);
        if (!requiresTableKey(qrForm.type.value)) qrForm.table_key.value = '';
    });
    qrForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await api('/qr-codes', { method: 'POST', body: JSON.stringify({
                branch_id: Number(qrForm.branch_id.value),
                type: qrForm.type.value,
                label: qrForm.label.value,
                table_key: requiresTableKey(qrForm.type.value) ? qrForm.table_key.value : null,
            }) });
            qrForm.reset();
            qrForm.querySelector('[data-table-key]').hidden = true;
            await loadQrAnalytics();
            toast('مسیر پایدار QR ساخته شد.');
        } catch (exception) { toast(exception.message); }
    });
    boot();
}

import '../css/admin.css';
import { buildReadyTranslations, nextAvailability } from './admin-data.js';

const tokenKey = 'denardi_admin_token';
const token = localStorage.getItem(tokenKey);
const headers = () => ({ Accept: 'application/json', 'Content-Type': 'application/json', Authorization: `Bearer ${localStorage.getItem(tokenKey) || ''}` });

async function api(path, options = {}) {
    const response = await fetch(`/api/admin/v1${path}`, { ...options, headers: { ...headers(), ...(options.headers || {}) } });
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

    const state = { context: null, categories: [], products: [], me: null };
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
    const render = () => {
        document.querySelector('[data-product-count]').textContent = state.products.length;
        document.querySelector('[data-category-count]').textContent = state.categories.length;
        document.querySelector('[data-branch-count]').textContent = state.context.branches.length;
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
    };
    async function loadProducts() {
        const products = await api('/products');
        state.products = products.data;
        render();
    }
    async function boot() {
        try {
            [state.me, state.context, state.categories] = await Promise.all([api('/me'), api('/business/context'), api('/categories')]);
            const products = await api('/products');
            state.products = products.data;
            translationInputs(document.querySelector('[data-category-translations]'), [['name', 'نام دسته']]);
            translationInputs(document.querySelector('[data-product-translations]'), [['name', 'نام محصول'], ['description', 'توضیح']]);
            render();
            document.querySelector('[data-loading]').hidden = true;
            dashboard.hidden = false;
        } catch (exception) {
            document.querySelector('[data-loading]').textContent = exception.message;
        }
    }
    document.querySelector('[data-open-product]').addEventListener('click', () => document.querySelector('[data-product-dialog]').showModal());
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
    boot();
}

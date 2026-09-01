import '../css/app.css';

const toggle = document.querySelector('.nav-toggle');
const navigation = document.querySelector('.site-nav');

toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    navigation?.classList.toggle('open', open);
});

document.querySelectorAll('.language-switcher a').forEach((link) => {
    link.addEventListener('click', () => {
        document.cookie = `denardi_locale=${link.getAttribute('lang')}; path=/; max-age=31536000; samesite=lax`;
    });
});

const menuSearch = document.querySelector('[data-menu-search]');
menuSearch?.addEventListener('input', () => {
    const query = menuSearch.value.trim().toLocaleLowerCase(document.documentElement.lang);
    let visible = 0;
    document.querySelectorAll('[data-product-name]').forEach((card) => {
        const matches = card.dataset.productName.includes(query);
        card.hidden = !matches;
        visible += matches ? 1 : 0;
    });
    const empty = document.querySelector('.search-empty');
    if (empty) empty.hidden = visible > 0;
});

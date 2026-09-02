import '../css/app.css';
import { normalizeSearchText, productMatchesSearch } from './public-ui.js';

if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));

const toggle = document.querySelector('.nav-toggle');
const navigation = document.querySelector('.site-nav');

toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    navigation?.classList.toggle('open', open);
});

navigation?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    toggle?.setAttribute('aria-expanded', 'false');
    navigation.classList.remove('open');
}));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && navigation?.classList.contains('open')) {
        navigation.classList.remove('open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.focus();
    }
});

document.querySelectorAll('.language-switcher a').forEach((link) => {
    link.addEventListener('click', () => {
        document.cookie = `denardi_locale=${link.getAttribute('lang')}; path=/; max-age=31536000; samesite=lax`;
    });
});

const menuSearch = document.querySelector('[data-menu-search]');
menuSearch?.addEventListener('input', () => {
    const query = normalizeSearchText(menuSearch.value, document.documentElement.lang);
    let visible = 0;
    document.querySelectorAll('[data-product-name]').forEach((card) => {
        const matches = productMatchesSearch(card.dataset.productName, query, document.documentElement.lang);
        card.hidden = !matches;
        visible += matches ? 1 : 0;
    });
    const empty = document.querySelector('.search-empty');
    if (empty) empty.hidden = visible > 0;
    document.querySelectorAll('[data-menu-category]').forEach((category) => {
        category.hidden = !category.querySelector('[data-product-name]:not([hidden])');
    });
    const status = document.querySelector('[data-search-status]');
    if (status) status.textContent = `${visible} ${status.dataset.resultLabel}`;
});

document.querySelectorAll('img[data-image-fallback]').forEach((image) => {
    image.addEventListener('error', () => {
        if (image.src.endsWith(image.dataset.imageFallback)) return;
        image.src = image.dataset.imageFallback;
        image.classList.add('is-fallback');
    });
});

const categoryLinks = [...document.querySelectorAll('.category-scroll a')];
if (categoryLinks.length && 'IntersectionObserver' in window) {
    const categoryObserver = new IntersectionObserver((entries) => {
        const current = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (!current) return;
        categoryLinks.forEach((link) => {
            const active = link.hash === `#${current.target.id}`;
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    }, { rootMargin: '-25% 0px -55%', threshold: [0, .2, .6] });
    document.querySelectorAll('[data-menu-category]').forEach((category) => categoryObserver.observe(category));
}

const analyticsRoot = document.querySelector('[data-menu-analytics]');
if (analyticsRoot && 'IntersectionObserver' in window) {
    const recorded = new Set();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const observer = new IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
            const element = entry.target;
            const key = `${element.dataset.analyticsType}:${element.dataset.analyticsSubject}`;
            if (recorded.has(key)) return;

            recorded.add(key);
            observer.unobserve(element);
            fetch(analyticsRoot.dataset.analyticsEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    type: element.dataset.analyticsType,
                    subject_public_id: element.dataset.analyticsSubject,
                    locale: analyticsRoot.dataset.analyticsLocale,
                    branch: analyticsRoot.dataset.analyticsBranch,
                }),
            }).catch(() => recorded.delete(key));
        });
    }, { threshold: 0.55 });

    document.querySelectorAll('[data-analytics-type][data-analytics-subject]').forEach((element) => observer.observe(element));
}

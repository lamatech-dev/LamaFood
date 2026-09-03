import '../css/app.css';
import '../css/menu.css';
import { activeCategoryId, normalizeSearchText, productMatchesSearch } from './public-ui.js';

if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));

const toggle = document.querySelector('.nav-toggle');
const navigation = document.querySelector('.site-nav');
const header = document.querySelector('.site-header');
const updateHeaderState = () => header?.classList.toggle('is-scrolled', window.scrollY > 20);
window.addEventListener('scroll', updateHeaderState, { passive: true });
updateHeaderState();

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
        const link = categoryLinks.find((item) => item.hash === `#${category.id}`);
        if (link) {
            link.hidden = category.hidden;
            link.classList.remove('active');
            link.removeAttribute('aria-current');
        }
    });
    const firstVisibleLink = categoryLinks.find((link) => !link.hidden);
    firstVisibleLink?.classList.add('active');
    firstVisibleLink?.setAttribute('aria-current', 'true');
    const status = document.querySelector('[data-search-status]');
    if (status) status.textContent = `${visible} ${status.dataset.resultLabel}`;
});

document.querySelectorAll('img[data-image-fallback]').forEach((image) => {
    const handleFailure = () => {
        if (image.src.endsWith(image.dataset.imageFallback)) return;
        image.removeAttribute('srcset');
        image.removeAttribute('sizes');
        image.src = image.dataset.imageFallback;
        image.classList.add('is-fallback');
    };
    image.addEventListener('error', handleFailure);
    if (image.complete && image.naturalWidth === 0) handleFailure();
});

const categoryLinks = [...document.querySelectorAll('.category-scroll a')];
if (categoryLinks.length) {
    const categoryRail = document.querySelector('.category-scroll');
    const menuControls = document.querySelector('.menu-controls');
    let pendingFrame = false;
    const updateActiveCategory = () => {
        pendingFrame = false;
        const sections = [...document.querySelectorAll('[data-menu-category]:not([hidden])')]
            .map((section) => ({ id: section.id, top: section.getBoundingClientRect().top }));
        const current = activeCategoryId(sections, (menuControls?.getBoundingClientRect().bottom ?? 160) + 48);
        categoryLinks.forEach((link) => {
            const active = !link.hidden && link.hash === `#${current}`;
            const changed = active && !link.classList.contains('active');
            link.classList.toggle('active', active);
            if (active) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
            if (changed && categoryRail) {
                const linkRect = link.getBoundingClientRect();
                const railRect = categoryRail.getBoundingClientRect();
                const offset = linkRect.left < railRect.left ? linkRect.left - railRect.left
                    : linkRect.right > railRect.right ? linkRect.right - railRect.right : 0;
                if (offset) categoryRail.scrollBy({ left: offset, behavior: 'auto' });
            }
        });
    };
    const scheduleCategoryUpdate = () => {
        if (pendingFrame) return;
        pendingFrame = true;
        requestAnimationFrame(updateActiveCategory);
    };
    window.addEventListener('scroll', scheduleCategoryUpdate, { passive: true });
    window.addEventListener('resize', scheduleCategoryUpdate);
    menuSearch?.addEventListener('input', scheduleCategoryUpdate);
    updateActiveCategory();
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

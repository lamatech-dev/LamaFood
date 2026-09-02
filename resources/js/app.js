import '../css/app.css';

if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));

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

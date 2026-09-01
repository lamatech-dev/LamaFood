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

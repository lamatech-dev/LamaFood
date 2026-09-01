<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Denardi Admin</title>@vite('resources/js/admin.js')</head>
<body class="admin-app dashboard-screen">
<aside class="admin-sidebar">
    <a class="admin-brand" href="/admin"><span>D</span><b>DENARDI</b></a>
    <nav><button class="active">نمای کلی</button><button data-section-link="menu">منوی دیجیتال</button><button data-section-link="content">محتوا</button><button data-section-link="media">رسانه</button><button disabled>QR و آمار <small>مرحله بعد</small></button></nav>
    <button class="logout-button" data-logout>خروج</button>
</aside>
<main class="admin-main">
    <header class="admin-topbar"><div><p class="admin-kicker">DENARDI · OPERATIONS</p><h1>پنل مدیریت</h1></div><div class="admin-identity"><span data-business-name>—</span><b data-user-name>—</b></div></header>
    <div class="admin-loading" data-loading>در حال بارگذاری پنل…</div>
    <div data-dashboard hidden>
        <section class="metric-grid"><article><span>محصول‌ها</span><strong data-product-count>0</strong></article><article><span>دسته‌ها</span><strong data-category-count>0</strong></article><article><span>ترجمه‌ها</span><strong>FA · EN · AR</strong></article><article><span>شعبه فعال</span><strong data-branch-count>0</strong></article></section>
        <section class="admin-panel" id="menu"><div class="panel-title"><div><p class="admin-kicker">MENU CATALOG</p><h2>محصول‌ها و قیمت شعبه</h2></div><button class="admin-button small" data-open-product>محصول جدید</button></div><div class="admin-table" data-products></div></section>
        <section class="admin-panel compact"><div class="panel-title"><div><p class="admin-kicker">CATEGORY</p><h2>دستهٔ جدید</h2></div></div><form class="inline-form" data-category-form><label>شناسه URL<input name="slug" required placeholder="coffee"></label><div data-category-translations class="translation-fields"></div><button class="admin-button" type="submit">ساخت و انتشار</button></form></section>
        <section class="admin-panel" id="content"><div class="panel-title"><div><p class="admin-kicker">LOCALIZED CMS</p><h2>صفحه‌ها و بلوک‌های محتوا</h2></div><button class="admin-button small" data-open-page>صفحه جدید</button></div><div class="admin-table" data-pages></div></section>
        <section class="admin-panel" id="media"><div class="panel-title"><div><p class="admin-kicker">MEDIA LIBRARY</p><h2>رسانه</h2></div></div><form class="media-form" data-media-form><label>تصویر<input name="file" type="file" accept="image/jpeg,image/png,image/webp" required></label><div data-media-translations class="translation-fields"></div><button class="admin-button" type="submit">بارگذاری</button></form><div class="media-grid" data-media></div></section>
    </div>
</main>

<dialog class="admin-dialog" data-product-dialog><form method="dialog" class="dialog-head"><h2>محصول جدید</h2><button aria-label="بستن">×</button></form><form data-product-form><label>دسته<select name="category_id" required></select></label><label>شناسه URL<input name="slug" required placeholder="iced-latte"></label><div data-product-translations class="translation-fields"></div><div class="form-grid"><label>قیمت شعبه (ریال)<input name="price_amount" type="number" min="0" required></label><label class="checkbox"><input name="is_new" type="checkbox"> جدید</label><label class="checkbox"><input name="is_best_seller" type="checkbox"> پرفروش</label></div><p class="form-error" data-product-error hidden></p><button class="admin-button" type="submit">ساخت، قیمت‌گذاری و انتشار</button></form></dialog>
<dialog class="admin-dialog" data-page-dialog><form method="dialog" class="dialog-head"><h2>صفحه جدید</h2><button aria-label="بستن">×</button></form><form data-page-form><label>شناسه URL<input name="slug" required placeholder="about"></label><label>قالب<input name="template" value="standard" required></label><div data-page-translations class="translation-fields"></div><p class="form-error" data-page-error hidden></p><button class="admin-button" type="submit">ساخت صفحه</button></form></dialog>
<dialog class="admin-dialog" data-block-dialog><form method="dialog" class="dialog-head"><h2>بلوک محتوای جدید</h2><button aria-label="بستن">×</button></form><form data-block-form><input name="page_id" type="hidden"><label>نوع بلوک<select name="type" required></select></label><label>جایگاه<input name="position" type="number" min="0" value="0" required></label><div class="schema-fields"><section><h3>تنظیمات مشترک</h3><div data-block-structure></div></section><section><h3>محتوای ترجمه‌شده</h3><div data-block-translations class="translation-fields"></div></section></div><p class="form-error" data-block-error hidden></p><button class="admin-button" type="submit">افزودن بلوک</button></form></dialog>
<div class="toast" data-toast hidden></div>
</body>
</html>

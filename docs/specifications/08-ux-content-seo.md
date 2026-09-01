# 08 — UX, Content, Localization & SEO

## Information Architecture

```text
/{locale}
/{locale}/menu
/{locale}/about
/{locale}/contact
/{locale}/privacy
/{locale}/products/{slug}   (در صورت فعال‌بودن صفحه جزئیات)
```

Localeهای V1 برابر `fa`, `en`, `ar` هستند. `fa` پیش‌فرض است. مسیر بدون locale با 302 بر اساس cookie/browser به locale فعال مناسب و سپس انتخاب کاربر پایدار می‌شود. Direction فقط از locale metadata خوانده می‌شود: `fa=rtl`, `en=ltr`, `ar=rtl`.

## Landing Denardi

- Hero با هویت Art · Coffee · Juice
- معرفی کوتاه کافه و فضای آن
- Menu preview و CTA اصلی «مشاهده منو»
- Gallery بهینه‌شده
- ساعت کاری
- location/map link
- Instagram و contact
- Footer با اطلاعات برند و Lamatech credit طبق قرارداد

Design tokenها از Charcoal، Teal و Electric Blue مشتق می‌شوند؛ contrast باید WCAG AA را پاس کند.

## Menu UX

- QR مستقیماً Menu را باز می‌کند.
- language switch فارسی/English/العربية همیشه قابل دسترس است.
- category chips بالای محتوا sticky و horizontally scrollable هستند.
- search با debounce کوتاه و بدون reload کامل انجام می‌شود.
- نتیجه search عنوان locale جاری را جست‌وجو می‌کند؛ حالت empty پیام و reset واضح دارد.
- Product card شامل تصویر اختیاری، نام، توضیح کوتاه، قیمت شعبه جاری و badge است.
- Sold Out از Branch settings می‌آید، واضح است و publication state محصول را تغییر نمی‌دهد.
- محصول بدون ترجمه `ready` در locale جاری در Public نمایش داده نمی‌شود و در Admin warning می‌گیرد؛ fallback عمومی خاموش است.

## Admin UX

### تغییر سریع قیمت

```text
Menu → Product → Branch settings → Edit price → Validate → Save
→ نمایش مقدار قبلی/جدید → Cache invalidate → Audit
```

### Sold Out

از list و Branch settings قابل تغییر است؛ optimistic UI فقط با rollback در خطا. تغییر موفق timestamp و actor نشان می‌دهد و publication state دست‌نخورده می‌ماند.

### Publish Content

Edit draft → validation → preview → publish confirmation → published revision. Unsaved changes و conflict هم‌زمان باید هشدار داشته باشند.

## Page/Block Content States

- Draft: فقط Admin/Preview
- Published: عمومی
- Inactive: از navigation و search عمومی حذف
- Archived: فقط تاریخچه Admin

## Product State Matrix

| Publication state | Branch availability | رفتار عمومی |
|---|---|---|
| `draft` | هر مقدار | نمایش داده نمی‌شود |
| `published` | `available` | نمایش عادی با قیمت Branch |
| `published` | `sold_out` | نمایش با وضعیت ناموجود |
| `inactive` | هر مقدار | موقتاً نمایش داده نمی‌شود |
| `archived` | هر مقدار | فقط تاریخچه Admin |

Block text در UI Admin با tabهای مستقل فارسی/English/العربية مدیریت می‌شود، اما backend آن را در `block_translations` جدا ذخیره و با schema مخصوص نوع Block validate می‌کند. completeness هر locale پیش از Publish همان locale نمایش داده می‌شود.

## Accessibility

- semantic landmarks و heading hierarchy
- keyboard navigation و focus visible
- touch target حداقل 44×44 CSS px
- alt text معنادار؛ تصویر تزئینی alt خالی
- رنگ تنها نشانه وضعیت نیست.
- Reduced Motion رعایت می‌شود.
- modalها focus trap و escape دارند.

## SEO

- title و description مستقل هر locale
- canonical self-referencing و hreflang `fa`, `en`, `ar`, `x-default`
- XML sitemap فقط URLهای Published/ready هر locale
- OpenGraph image مشخص و بهینه
- JSON-LD بر اساس `CafeOrCoffeeShop`/`LocalBusiness` و داده تأییدشده
- Product/Menu schema فقط وقتی محتوای صفحه با schema تطابق دارد.
- URL حذف‌شده در صورت جایگزین 301، در غیر این صورت 410/404 مناسب
- Preview و Admin دارای `noindex` و محافظت Auth هستند.

## محتوای مورد نیاز پیش از Go-live

- logo/favicon و تصاویر مجاز از نظر حق استفاده
- نام رسمی فارسی/انگلیسی/عربی
- Hero/About در هر سه زبان
- تلفن، آدرس، map pin، Instagram و ساعت کاری
- نام/توضیح/قیمت/عکس محصولات
- ingredients/allergen notice در صورت ادعا
- Privacy متن متناسب با Analytics

## QR Print Deliverables

- SVG و PDF vector برای هر میز و ورودی
- PNG high-resolution برای استفاده دیجیتال
- label خوانا مثل `Table 01`
- short URL انسانی برای بازیابی در صورت مشکل Scan
- تست چاپ واقعی در اندازه هدف و نور محیط کافه

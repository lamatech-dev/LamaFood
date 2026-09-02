# 01 — Denardi V1 Scope & Acceptance Criteria

## هدف Release

Denardi V1 باید یک وب‌سایت سه‌زبانه، سریع و قابل نصب به‌صورت PWA ارائه کند که صاحب کسب‌وکار بتواند از موبایل محتوا و منوی دیجیتال را بدون دخالت توسعه‌دهنده مدیریت کند. Localeهای الزامی `fa`، `en` و `ar` هستند. این Release اولین Vertical Slice قابل استفاده از Lamatech Core است.

## کاربران هدف

- بازدیدکننده سایت و منو
- Business Owner
- Content Editor
- Lamatech Super Admin

حساب داخلی Lamatech با display identity برابر `Godfather` بالاترین سطح supervisory هر Instance است. این حساب Business user نیست، در UI/API مدیریت کاربران مشتری یا count/search آن‌ها نمایش داده نمی‌شود و از همان Gate/RBAC رسمی bypass می‌گیرد؛ هیچ backdoor URL یا master password ندارد.

Roleهای Manager، Cashier و Staff در مدل Permission وجود دارند، ولی UI اختصاصی آن‌ها در V1 الزامی نیست.

## قابلیت‌های داخل Scope

### Public Website

- Home/Landing با Blockهای کنترل‌شده
- صفحات About، Contact، Menu و Privacy
- نمایش اطلاعات تماس، آدرس، نقشه، ساعت کاری و شبکه‌های اجتماعی
- طراحی Responsive از عرض 320px به بالا
- مسیرهای بدون prefix برای فارسی پیش‌فرض (`/`، `/menu`، `/about` و...) و مسیرهای `/en/...` و `/ar/...` برای انگلیسی و عربی؛ `/fa/...` فقط redirect دائمی سازگار با لینک‌های قدیمی است.
- direction از locale metadata: `fa=rtl`, `en=ltr`, `ar=rtl`
- Theme اختصاصی Denardi
- حفظ هویت Art · Coffee · Juice با پالت Charcoal + Teal + Electric Blue
- لینک مستقیم Instagram و موقعیت روی نقشه
- SEO metadata، sitemap، robots.txt، canonical، hreflang و OpenGraph
- LocalBusiness یا CafeOrCoffeeShop schema بر اساس داده واقعی
- PWA manifest، icons، installability و offline shell

### CMS و Media

- ایجاد/ویرایش Page و Block
- Draft، Preview و Publish
- فعال/غیرفعال‌کردن و مرتب‌سازی Blockها
- Media upload با validation، thumbnail و WebP
- Alt text مستقل برای هر سه locale
- جلوگیری از حذف Media در حال استفاده

### Digital Menu

- Category و Subcategory
- Product catalog در سطح Business با عنوان، توضیح، تصویر و ترتیب
- Product↔Branch settings برای قیمت و availability؛ تفاوت شعبه باعث duplicate شدن Product نمی‌شود.
- Variant و Add-on اختیاری
- Featured، New و Best Seller
- publication state مستقل: `draft`, `published`, `inactive`, `archived`
- Branch availability مستقل: `available`, `sold_out`
- جست‌وجوی locale-aware بر اساس عنوان محصول
- navigation دسته‌ها به‌صورت horizontal scroll روی موبایل
- محتوای مستقل فارسی/انگلیسی/عربی برای Category، Product، Variant، Add-on، ingredients و allergen notice
- نمایش فقط آیتم‌ها و دسته‌های Published و فعال

### QR و Analytics

- QR ورودی/عمومی منو با URL پایدار
- QR مجزا و قابل‌ردیابی برای هر میز، بدون قابلیت سفارش
- ثبت Scan، زمان، locale، table و device class
- شمارش Page View، Menu View، Category View و Product View
- داشبورد ساده برای بازه امروز، 7 روز و 30 روز
- حذف Botهای شناخته‌شده و عدم ذخیره IP کامل

### Admin PWA

- Login، logout، reset password
- Dashboard موبایل
- مدیریت Page، Block، Media، Category و Product
- تغییر سریع Price و Sold Out
- مشاهده Analytics پایه
- نمایش وضعیت آخرین Backup و نسخه Core

### Reliability

- Backup روزانه DB
- Full backup هفتگی شامل DB، uploads و manifest غیرحساس؛ بدون `.env`/APP key/plaintext secrets
- Restore point پیش از Release/Migration
- Audit برای login، publish، تغییر قیمت، Sold Out، Role و Backup/Restore
- Health check برای app، DB، storage، queue، scheduler و backup freshness

## خارج از Scope

- سبد خرید، سفارش، پرداخت و Kitchen
- Reservation، CRM، Loyalty و Inventory
- ارسال SMS/Email campaign و Push
- Multi-branch UI
- Page Builder آزاد
- Native app
- Offline editing یا conflict resolution
- Control Plane مرکزی و Remote Update خودکار
- Runtime Module installer، package download، marketplace و uninstall automation
- Campaign QR، مگر با Change Request مبتنی بر نیازمندی امضاشده Denardi
- License server، remote enforcement و قطع خودکار سرویس بر اساس License
- AI Assistant عملیاتی؛ فقط Contract، feature flag، usage schema و audit type آماده می‌شوند

## Acceptance Criteria

### عملکردی

1. Owner می‌تواند Product catalog را با سه ترجمه و تصویر بسازد، برای Branch قیمت تعیین کند، منتشر کند و در Public Menu ببیند.
2. تغییر قیمت از Product↔Branch settings پس از ذخیره با invalidate شدن cache حداکثر در 10 ثانیه عمومی می‌شود.
3. `sold_out` بدون تغییر publication state یا حذف Product فعال و قابل بازگشت است.
4. QR عمومی همیشه به URL canonical منو هدایت می‌شود و locale را از انتخاب کاربر/مرورگر تعیین می‌کند.
5. QR هر میز URL و شناسه پایدار خودش را دارد؛ Scan در Analytics به همان میز نسبت داده می‌شود، اما Order ساخته نمی‌شود.
6. جست‌وجوی منو با عنوان فارسی، انگلیسی و عربی در زبان جاری کار می‌کند و پاک‌کردن جست‌وجو همه دسته‌های فعال را برمی‌گرداند.
7. QR میز Scan را فقط یک‌بار در پنجره deduplication سی‌دقیقه‌ای برای همان fingerprint ناشناس ثبت می‌کند.
8. Editor نمی‌تواند Role، Backup، Module یا Business settings حساس را تغییر دهد.
9. Preview Draft برای کاربر ناشناس با پاسخ 404 یا 403 غیرقابل مشاهده است.
10. حذف Category دارای Product تا انتقال/حذف محصولات رد می‌شود.
11. تغییرات حساس در Audit با actor، زمان، action، target و before/after ثبت می‌شوند.
12. در نبود Queue، سایت عمومی همچنان کار می‌کند و Jobها قابل retry هستند.
13. Product `draft`, `inactive` یا `archived` عمومی نیست؛ Product `published + sold_out` نمایش داده می‌شود ولی ناموجود است.
14. Full Backup معمولی هیچ plaintext production secret، `.env` یا APP key را در archive ندارد.
15. `/` (فارسی)، `/en` و `/ar` با direction برگرفته از metadata render می‌شوند؛ RTL به شرط خاص فارسی وابسته نیست و `/fa` به `/` redirect می‌شود.
16. Admin برای هر entity وضعیت تکمیل ترجمه هر سه locale را نشان می‌دهد و اجازه ویرایش مستقل دارد.
17. هیچ متن فارسی/انگلیسی به‌صورت fallback خاموش داخل صفحه عربی عمومی نمایش داده نمی‌شود.

### کیفیت

- Lighthouse هدف روی موبایل Production: Performance >= 85، Accessibility >= 90، Best Practices >= 90، SEO >= 95.
- LCP هدف <= 2.5s و CLS <= 0.1 روی اتصال موبایل متعارف.
- هیچ آسیب‌پذیری Critical/High شناخته‌شده در dependency scan هنگام Release وجود ندارد.
- همه Flowهای P0 در Chrome، Safari iOS و Chrome Android تست می‌شوند.
- صفحات فارسی/عربی RTL و انگلیسی LTR بدون overflow افقی در 320، 375، 768، 1024 و 1440px هستند.
- Restore آزمایشی آخرین Full Backup روی Staging موفق است.

## Definition of Done برای V1

- تمام معیارهای بالا Pass شده‌اند.
- محتوای نهایی Denardi وارد و تأیید شده است.
- Production، Staging، monitoring و backup مقصد خارجی فعال‌اند.
- Runbook استقرار و بازیابی با یک Release واقعی تمرین شده است.
- Owner یک جلسه تحویل و راهنمای کوتاه مدیریت محتوا دریافت کرده است.
- Known Issues و موارد Phase بعد ثبت شده‌اند.
- Landing/Home، Menu، About، Contact، navigation و core SEO metadata در هر سه locale `ready` و تأیید شده‌اند.

## Traceability با Proposal مشتری

نیازمندی‌های `DEN-01` تا `DEN-12` در `00-denardi-client-requirements.md` باید در UAT پوشش داده شوند. هیچ‌یک از آن‌ها بدون Change Request از Release حذف نمی‌شود.

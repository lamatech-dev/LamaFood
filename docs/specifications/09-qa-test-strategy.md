# 09 — QA & Test Strategy

## سطوح تست

- Unit: value object، validation، permission، pricing و event payload
- Feature: HTTP/API، policy، publish، upload، QR و analytics
- Integration: DB، storage، queue، backup adapter و image processing
- Browser/E2E: مسیرهای حیاتی Public/Admin
- Visual: RTL/LTR و breakpointهای هدف
- Operational: deploy، migration، backup، restore و rollback

## Flowهای P0

1. بازکردن منو از QR میز در فارسی، انگلیسی و عربی
2. جست‌وجو و navigation دسته‌ها روی موبایل
3. Login Owner
4. افزودن Product catalog سه‌زبانه با تصویر بدون Branch duplication
5. تنظیم/تغییر قیمت در Product↔Branch settings و مشاهده عمومی
6. تغییر `available ↔ sold_out` بدون تغییر publication state
7. انتقال Product بین `draft/published/inactive/archived` و بررسی visibility matrix
8. ساخت Block با structure مشترک و ترجمه‌های مستقل، validation و Publish locale
9. Draft → Preview → Publish صفحه
10. تولید/دانلود General Menu QR و QR هر میز
11. Backup بدون secret، provision جداگانه secret، verify و restore روی Staging
12. اعمال migration و rollback طبقه‌بندی‌شده

## Matrix مرورگر

- Chrome Android: دو نسخه اصلی اخیر
- Safari iOS: دو نسخه اصلی اخیر
- Chrome Desktop و Edge: دو نسخه اخیر
- Safari macOS: نسخه اخیر
- Firefox: نسخه اخیر برای functional compatibility

## داده و Localization

- متن کوتاه/بلند، اعداد فارسی/لاتین، emoji و نیم‌فاصله
- نام تکراری، slug conflict و ترجمه ناقص
- RTL/LTR mixed content
- قیمت صفر/بزرگ/نامعتبر
- category عمیق‌تر از سطح پشتیبانی‌شده باید رد شود.

## Security Tests

- authz برای هر endpoint و cross-role access
- اثبات Gate bypass برای Godfather، نبود role قابل‌نمایش، حذف آن از list/search/count و منع mutation از Business user-management
- bootstrap/rotation Godfather فقط با env و ثبت audit؛ secret نباید در source، response، log یا fixture واقعی دیده شود.
- CSRF، session fixation و rate limit
- XSS در title/description/alt و URLها
- upload spoofing، oversized image و SVG خطرناک
- mass assignment و IDOR
- عدم نشت secret/stack trace در response/log
- تأیید نبود `.env`, `APP_KEY` و plaintext Provider secrets در همه archiveها
- dependency scan و secret scan

## Performance Tests

- dataset آزمایشی حداقل 30 category، 500 product و 5,000 media metadata
- Public menu p95 و query count ثبت می‌شود.
- search و category filter با cache سرد/گرم تست می‌شوند.
- analytics ingestion نباید response نمایش منو را block کند.

## Accessibility و Visual QA

- automated axe/Lighthouse به‌علاوه keyboard-only check
- screen-reader smoke test روی navigation/menu
- contrast و focus state
- snapshot در 320، 375، 768، 1024 و 1440px برای `fa/en/ar`
- assert جهت از metadata: فارسی/عربی RTL و انگلیسی LTR، بدون Persian-specific conditional
- تست incomplete translation: warning Admin، عدم public language mixing و عدم sitemap entry برای locale ناآماده
- نبود horizontal overflow و layout shift محسوس

## Release Gates

- همه P0 Pass
- P1 بدون workaround قابل قبول: صفر؛ یا تأیید Release owner
- Critical/High security: صفر
- migration و restore drill موفق
- تست Product/Branch separation، lifecycle matrix و Block translation schema موفق
- تأیید اینکه Campaign QR، runtime Module installer و license enforcement در artifact/UI وجود ندارند.
- Lighthouse مطابق Scope
- Staging sign-off محتوایی از Denardi
- changelog، rollback note و known issues کامل

## Bug Severity

- P0: سایت/منو از دسترس خارج، از دست‌رفتن داده، bypass امنیت
- P1: Flow اصلی خراب، قیمت غلط، Publish یا Login غیرقابل استفاده
- P2: مشکل با workaround، مرورگر محدود یا UI مهم
- P3: polish، متن یا ناسازگاری جزئی

## UAT Checklist Denardi

- اطلاعات برند و تماس صحیح
- همه دسته‌ها/قیمت‌ها/ترجمه‌ها تأییدشده
- QR تمام میزها به مقصد درست می‌روند.
- Owner از موبایل قیمت و موجودی را تغییر می‌دهد.
- محتوا و تصاویر مجوز انتشار دارند.
- مسئول Denardi تأیید Go-live را مکتوب ثبت می‌کند.

# 06 — Security, Privacy & AI Governance

## مدل تهدید

دارایی‌های حساس شامل حساب Owner، داده کسب‌وکار، Media خصوصی، Backup، Provider secrets، License و Audit است. مهاجمان محتمل: credential stuffing، کاربر داخلی با Permission ناکافی، upload مخرب، bot analytics، dependency compromise و prompt injection آینده.

## کنترل هویت و دسترسی

- password حداقل 12 نویسه یا passphrase؛ hash با Argon2id/Bcrypt تنظیم‌شده
- session rotation پس از login و privilege change
- cookie با `Secure`, `HttpOnly`, `SameSite=Lax`
- inactivity timeout پنل 8 ساعت و absolute timeout هفت روز
- rate limit و progressive delay برای login
- OTP/TOTP و 2FA در Amendment فعلی V1 صراحتاً Deferred است؛ اضافه‌شدن آن نیازمند scope/تصمیم جداگانه است.
- Permission روی Controller، Service action و query scope اعمال می‌شود.
- re-authentication برای Restore، تغییر Role، Provider secret و عملیات مخرب
- Godfather از Gate استاندارد و marker داخلی استفاده می‌کند؛ hidden route، master password یا hardcoded credential condition ممنوع است.
- credential Godfather فقط از env محلی bootstrap/rotate می‌شود، `.env.example` فقط placeholder امن دارد و actionهای مهم آن audit می‌شوند.

## امنیت Web

- CSRF، output escaping و CSP مرحله‌ای
- HSTS پس از اطمینان از HTTPS کامل
- `frame-ancestors`, `nosniff`, Referrer-Policy و Permissions-Policy
- validation سمت server؛ client validation فقط UX است.
- URL خروجی Instagram/Map فقط scheme مجاز دارد.
- logها فاقد password، cookie، token، secret و محتوای حساس‌اند.

## Upload و Media

- allowlist: JPEG، PNG، WebP و در صورت نیاز SVG sanitize‌شده
- تشخیص MIME از محتوا، نه extension
- محدودیت پیش‌فرض تصویر 10MB و pixel count برای جلوگیری از decompression bomb
- نام فایل تصادفی؛ ذخیره خارج از executable path
- strip metadata مکانی از تصاویر مشتق‌شده
- فایل تا پایان validation در quarantine است.

## Secret Management

- `.env` خارج از public root و با permission محدود
- secretهای ذخیره‌شده در DB با application key رمزنگاری می‌شوند.
- rotation procedure برای DB، mail، storage، SMS، Payment و AI تعریف می‌شود.
- secret هر Instance مستقل است.

## Privacy و Analytics

- raw IP ذخیره نمی‌شود؛ فقط prefix کوتاه در Audit امنیتی با retention محدود در صورت نیاز.
- session analytics با hash غیرقابل برگشت و salt چرخشی ساخته می‌شود.
- شناسه تبلیغاتی یا cross-site tracking در V1 وجود ندارد.
- privacy page نوع داده، هدف، retention و راه تماس را توضیح می‌دهد.
- retention پیشنهادی: raw analytics برابر 13 ماه، aggregate بلندمدت؛ Audit حداقل 24 ماه (`TBD-BUSINESS`).

## Backup Security

- encryption قبل از خروج از server
- مقصد خارجی با credential محدود و write-only تا حد امکان
- checksum، retention lock و verify دوره‌ای
- دسترسی Restore فقط برای Lamatech Super Admin و ثبت کامل در Audit
- Full Backup عادی هرگز `.env`، `APP_KEY`، private key، access token یا plaintext production secret را داخل archive قرار نمی‌دهد.
- DB backup ممکن است فیلدهای secret را فقط به‌صورت application-encrypted ciphertext داشته باشد؛ کلید decrypt داخل همان Backup نیست.
- archive فقط یک `configuration-manifest` غیرحساس شامل نام متغیرهای لازم، provider type و secret version/reference دارد، نه value آن‌ها.
- V1 secret recovery با re-provision دستی از password manager/escrow رمزنگاری‌شده و تحت دسترسی جدا انجام می‌شود.
- `APP_KEY` و credentialهای recovery در vault/مدیر رمز عبور سازمانی خارج از سرور و Backup نگهداری می‌شوند.
- اگر در آینده secret export لازم شد، باید envelope جداگانه با کلید/دسترسی مستقل، audit و expiration داشته باشد؛ هرگز در archive اصلی ادغام نمی‌شود.

## AI Boundary

در V1 فقط Gateway contract، feature flags، permission، usage و audit type پیاده می‌شوند. هنگام فعال‌سازی AI:

```text
User → Feature → AI Gateway → Context Builder → Tool Registry → Provider
                         ↘ Approval Queue برای Write
```

### قواعد

- مدل مستقیم SQL، filesystem، HTTP عمومی یا secret نمی‌بیند.
- Context بر اساس Business، User permission و purpose محدود می‌شود.
- هر Tool schema، permission، timeout، rate limit و audit policy دارد.
- Read tool فقط داده لازم را برمی‌گرداند.
- Write tool همیشه draft/confirmation ایجاد می‌کند؛ اجرای مستقیم ممنوع است.
- price و availability فقط از Product↔Branch settings رسمی، و ingredient/allergen فقط از Product translation تأییدشده پاسخ داده می‌شوند.
- نبود داده با پاسخ «اطلاعات ثبت نشده» اعلام می‌شود.
- پیام مشتری و محتوای CMS untrusted محسوب می‌شوند و نمی‌توانند policy/tool permission را تغییر دهند.
- Provider، model، prompt version، tool version، usage و نتیجه ثبت می‌شود؛ prompt کامل به‌صورت پیش‌فرض ذخیره نمی‌شود.
- hard monthly quota، per-user limit و kill switch برای هر Business وجود دارد.

## AI Approval

Action شامل summary فارسی، target، before/after، side effects، estimated external cost و expiration است. Approver باید Permission مستقل داشته باشد. پس از تغییر داده، approval قبلی stale و غیرقابل اجرا می‌شود.

## Incident Response

1. detect و ثبت severity
2. محدودسازی session/key/feature آسیب‌دیده
3. حفظ log و evidence
4. ارزیابی داده تحت تأثیر
5. اصلاح، rotation و recovery
6. اطلاع‌رسانی طبق تعهد قراردادی/قانونی
7. postmortem بدون سرزنش و اقدام پیشگیرانه

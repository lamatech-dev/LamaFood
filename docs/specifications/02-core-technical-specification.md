# 02 — Lamatech Core Technical Specification v1.0

## معماری کلان

```text
Browser
  ├─ Public Blade UI
  └─ React Admin SPA
          │
       Laravel HTTP/API
          │
  ┌───────┼────────┐
 Core   Modules   Adapters
  │        │         │
 MySQL   Queue    Storage/Providers
```

یک Deployment شامل یک Laravel application، یک Admin bundle و یک دیتابیس مشتری است. ارتباط بین Moduleها فقط از طریق Contract، Event یا API داخلی تعریف‌شده مجاز است.

## ساختار Repository

```text
app/
  Core/
    Auth/
    Business/
    Cms/
    Media/
    Localization/
    Seo/
    Modules/
    Audit/
    Backup/
    Health/
    Analytics/
    Ai/
  Support/
modules/
  Menu/
  Qr/
resources/
  admin/
  public/
  views/
routes/
  web.php
  api.php
  admin.php
database/
  migrations/
  seeders/
tests/
  Unit/
  Feature/
  Browser/
```

## Boundary Rules

- Controller فقط orchestration می‌کند و منطق دامنه در Action/Service قرار می‌گیرد.
- دسترسی مستقیم Module A به Model داخلی Module B ممنوع است.
- Cross-module read از Query Contract و write از Command/Event انجام می‌شود.
- Eventهای Domain پس از commit منتشر می‌شوند؛ Listener باید idempotent باشد.
- Public response نباید به availability سرویس خارجی وابسته باشد.

## Core Services

### Business

Business identity، Branch پایه، locale، currency، timezone، contact، theme و feature flags را نگهداری می‌کند.

Localeها data/config-driven هستند. Foundation یک Registry/metadata با فیلدهای `code`, `native_name`, `direction`, `is_default`, `is_enabled`, `position` دارد. Seed/initial config Denardi:

```text
fa | فارسی   | rtl | default
en | English | ltr
ar | العربية | rtl
```

Routing، HTML `lang/dir`، Admin tabs و formatterها فقط این metadata را مصرف می‌کنند و هیچ `if locale === fa then rtl` مجاز نیست.

### Auth/RBAC

- Session-based authentication
- Laravel Sanctum برای Admin API
- Policy/Gate روی هر resource
- Permission نام‌گذاری‌شده به شکل `domain.action`
- deny-by-default
- حساب `Godfather` یک User داخلی با `business_id = null` و marker محافظت‌شده است؛ `Gate::before` فقط برای این marker مجوز instance-wide می‌دهد.
- Godfather هیچ role مشتری قابل‌نمایش/قابل‌تخصیص ندارد و همه queryهای Business user-management باید scope حذف آن را اعمال کنند.
- bootstrap و password rotation فقط از command داخلی با `LAMATECH_GODFATHER_USERNAME/PASSWORD` انجام می‌شود؛ credential در source، migration، seeder، docs یا bundle قرار نمی‌گیرد.
- actionهای مهم Godfather مانند login، credential rotation و mutationهای مدیریتی در Audit داخلی ثبت می‌شوند.

### CMS

- Page → ordered Blocks
- Block type دارای `structureSchema`، `translationSchema` و renderer ثبت‌شده است.
- داده غیرقابل ترجمه مانند media IDs، layout variant، alignment و CTA target در `blocks.structure_json` ذخیره می‌شود.
- متن قابل ترجمه در `block_translations.content_json` با یک رکورد مستقل برای هر `(block_id, locale)` ذخیره می‌شود.
- قرار دادن کلید locale مانند `fa`/`en` داخل `structure_json` یا JSON آزاد ممنوع است.
- هر `content_json` دقیقاً مقابل `translationSchema` همان Block type اعتبارسنجی می‌شود؛ default locale باید همه fieldهای required را داشته باشد.
- locale ثانویه می‌تواند Draft ناقص باشد، اما Publish صفحه در آن locale فقط با عبور از validation کامل همان locale ممکن است.
- برای Denardi V1 هر سه locale مستقل‌اند؛ `fa` default است و `en/ar` locale ثانویه ولی الزامی برای Go-live هستند.
- lifecycle: draft → published → archived
- published snapshot برای جلوگیری از نمایش حالت نیمه‌ویرایش‌شده

نمونه قرارداد Hero:

```text
structure_json: { mediaId, variant, alignment, ctaTarget }
fa content_json: { eyebrow, title, body, ctaLabel }
en content_json: { eyebrow, title, body, ctaLabel }
ar content_json: { eyebrow, title, body, ctaLabel }
```

### V1 Block Schema Registry

| Block type | `structure_json` غیرقابل ترجمه | `content_json` هر locale |
|---|---|---|
| `hero` | `mediaId`, `variant`, `alignment`, `ctaTarget` | `eyebrow`, `title*`, `body`, `ctaLabel` |
| `about` | `mediaIds`, `variant` | `heading*`, `body*` |
| `gallery` | `mediaIds`, `layout` | `heading`, `caption` |
| `menu_preview` | `categoryIds`, `itemLimit`, `variant` | `heading*`, `intro`, `ctaLabel*` |
| `location` | `lat`, `lng`, `mapUrl`, `variant` | `heading*`, `address*`, `directionsLabel` |
| `contact` | `phone`, `instagramUrl`, `variant` | `heading*`, `body`, `phoneLabel`, `instagramLabel` |
| `cta` | `target`, `variant`, `style` | `heading*`, `body`, `label*` |
| `footer` | `logoMediaId`, `socialUrls` | `tagline`, `copyrightText` |

علامت `*` یعنی required برای `translation_state=ready`. Schemaها `additionalProperties=false` دارند. Block خارج از این Registry برای V1 نیازمند Specification/Change Request است.

### Missing Translation Policy

- `ready` بودن فقط با تکمیل fieldهای `*` همان locale ممکن است؛ fieldهای بدون `*` optional و در نبود ترجمه خالی render می‌شوند، نه با متن locale دیگر.
- Public route یک Page locale را فقط وقتی render می‌کند که Page translation و Block translationهای required آن locale `ready` باشند.
- Category/Product/Variant/Add-on فاقد translation `ready` در locale جاری در همان locale نمایش داده نمی‌شود و در Admin warning واضح دارد.
- Public fallback خاموش است؛ fallback به `fa` فقط در Admin/Preview و با برچسب صریح «fallback preview» مجاز است.
- Go-live Denardi نیازمند `ready` بودن Landing/Home، Menu entities، About، Contact، navigation و core SEO در `fa/en/ar` است.

### Catalog و Branch Menu State

- Product، translation، media association و merchandising flags در سطح Business هستند.
- `product_branch_settings` تنها محل قیمت پایه و availability شعبه است.
- publication state متعلق به Product است؛ availability متعلق به رابطه Product↔Branch.
- Product به‌دلیل تفاوت قیمت یا `sold_out` شعبه duplicate نمی‌شود.
- V1 یک Branch دارد، اما query عمومی همیشه Product را با Branch settings همان context join می‌کند.

### Cache

- Tagهای منطقی: `business`, `page:{id}`, `menu`, `product:{id}`
- هر write موفق cache مرتبط را invalidate می‌کند.
- Cache failure نباید write یا Public view را fail کند.

### Events

حداقل Eventها:

```text
PagePublished
ProductCreated
ProductUpdated
ProductPublicationStateChanged
BranchProductPriceChanged
BranchProductAvailabilityChanged
QrScanned
MediaUploaded
BackupCompleted
BackupFailed
HealthCheckFailed
ModuleStateChanged
```

Envelope استاندارد Event شامل `event_id`, `event_name`, `occurred_at`, `actor_id`, `business_id`, `branch_id`, `subject`, `payload_version` است.

### Audit

Audit append-only است. داده حساس، token، password و secret در before/after ذخیره نمی‌شود. تغییر قیمت و Permission همیشه audit می‌شود.

### Health

- `/up`: فقط liveness و بدون اطلاعات حساس
- endpoint امضاشده Admin: DB، storage، queue، scheduler، backup، versions
- وضعیت‌ها: healthy، degraded، critical

## Configuration

- تنظیمات deploy در Environment
- تنظیمات business در DB
- feature flagها در DB با default امن در code
- Provider secretها encrypted-at-rest و خارج از response/log
- Instance/License metadata در V1 محلی و informational است؛ Core به license server یا remote enforcement وابسته نیست.

## V1 Module Delivery Profile

Menu و QR به‌صورت bundled source/artifact همراه Application Deploy می‌شوند. Manifest و Contractها برای boundary، versioning و آینده حفظ می‌شوند، اما Denardi V1 شامل دانلود package، signature service، runtime installer، marketplace، remote uninstall یا dependency solver عمومی نیست. فعال‌سازی Module فقط از allowlist موجود در build و state/config کنترل‌شده انجام می‌شود.

## Error Handling

- response API دارای stable error code است.
- exception داخلی با correlation ID log می‌شود.
- Production stack trace به کاربر نمایش داده نمی‌شود.
- خطای validation پاسخ 422؛ unauthenticated برابر 401؛ forbidden برابر 403؛ conflict برابر 409.

## Versioning

- Core و Moduleها Semantic Versioning دارند.
- breaking contract فقط در major version.
- DB schema version از code version جدا ثبت می‌شود.
- هر Release شامل changelog، migration note و rollback classification است.

## Performance Budget

- Public HTML p95 در سطح application <= 500ms بدون محاسبه network.
- Admin listها pagination دارند؛ default برابر 25 و max برابر 100.
- تصاویر responsive و lazy-loaded هستند، جز تصویر LCP.
- Queryهای list نباید N+1 داشته باشند و query count در تست‌های کلیدی budget می‌شود.

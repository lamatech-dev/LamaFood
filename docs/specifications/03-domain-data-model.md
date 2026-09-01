# 03 — Domain Data Model

## قواعد عمومی

- Primary key داخلی: `BIGINT UNSIGNED`.
- شناسه قابل نمایش بیرونی: ULID/UUID جداگانه (`public_id`).
- زمان‌ها در DB به UTC و نمایش بر اساس `Asia/Tehran`.
- مبالغ به‌صورت integer در کوچک‌ترین واحد قراردادی ذخیره می‌شوند؛ V1 واحد نمایشی IRR/IRT در Business config صریح است.
- جداول محتوایی `created_by`, `updated_by`, timestamps و در صورت نیاز soft delete دارند.
- Translationها در جدول جدا ذخیره می‌شوند تا query، validation و SEO شفاف باشد.

## هویت و دسترسی

### businesses

`id`, `public_id`, `name`, `slug`, `default_locale`, `currency`, `timezone`, `status`, `settings_json`

### branches

`id`, `business_id`, `name`, `code`, `phone`, `address`, `lat`, `lng`, `working_hours_json`, `is_active`

V1 دقیقاً یک Branch فعال دارد، ولی Product، QR و Analytics از ابتدا قابلیت ارجاع به Branch را دارند.

### business_locales

`id`, `business_id`, `locale`, `native_name`, `direction`, `is_default`, `is_enabled`, `position`

- unique: `(business_id, locale)`
- `direction`: `rtl` یا `ltr`
- Denardi seed: `fa/فارسی/rtl/default`, `en/English/ltr`, `ar/العربية/rtl`
- service invariant: هر Business دقیقاً یک locale پیش‌فرض فعال دارد.
- routing و presentation از این جدول/config Registry می‌خوانند؛ direction از نام locale استنتاج نمی‌شود.

### users / roles / permissions

از ساختار استاندارد RBAC با pivotهای scoped به Business استفاده می‌شود. Userهای عادی `business_id` دارند. حساب instance-level Lamatech با identity `Godfather` دارای `business_id = null` و marker غیرقابل mass-assign است و از Gate رسمی بالاترین دسترسی را می‌گیرد؛ role قابل‌نمایش مشتری برای آن ساخته نمی‌شود.

تمام relation/query/API مربوط به Users، Staff، Roles، Permission screens، search و count مشتری باید scope `business-visible` را اعمال و Godfather را حذف کند. Policyهای Business اجازه view/edit/delete/disable/password/permission mutation روی Godfather نمی‌دهند. ایجاد یا rotate آن فقط با command Lamatech و env secret انجام می‌شود.

## CMS

### pages

`id`, `business_id`, `slug`, `template`, `status`, `published_revision_id`, `published_at`, `sort_order`

### page_translations

`page_id`, `locale`, `title`, `meta_title`, `meta_description`, `og_title`, `og_description`, `translation_state`

`translation_state` یکی از `draft` یا `ready` است. Publish صفحه برای locale فقط وقتی مجاز است که Page translation و تمام Block translationهای required آن locale `ready` باشند.

### page_revisions

snapshot کامل قابل immutable برای Preview/Publish و rollback محتوا.

### blocks

`id`, `page_id`, `type`, `position`, `is_enabled`, `structure_json`

`structure_json` فقط داده غیرقابل ترجمه مانند `variant`, `media_id`, `alignment` و targetهای داخلی را نگه می‌دارد و مقابل `structureSchema` نوع Block اعتبارسنجی می‌شود.

### block_translations

`id`, `block_id`, `locale`, `content_json`, `translation_state`, `validated_at`

- unique: `(block_id, locale)`
- `content_json` فقط fieldهای قابل ترجمه تعریف‌شده در `translationSchema` نوع Block را می‌پذیرد.
- `translation_state`: `draft` یا `ready`؛ حالت `ready` فقط پس از validation کامل همان locale مجاز است.
- default locale برای Publish صفحه باید `ready` باشد. انتشار یک locale ثانویه نیز نیازمند `ready` بودن تمام Block translationهای لازم در همان locale است.
- Denardi Go-live علاوه بر default، `en` و `ar` را نیز برای تمام محتوای عمومی الزامی می‌کند.
- locale key داخل `structure_json` یا nestingهایی مانند `{ "fa": ..., "en": ... }` ممنوع است.
- schema version مورد استفاده در Page revision ثبت می‌شود تا snapshot قدیمی قابل render باشد.

## Media

### media

`id`, `business_id`, `disk`, `path`, `mime`, `size`, `width`, `height`, `checksum`, `status`, `uploaded_by`

### media_translations

`media_id`, `locale`, `alt`, `title`, `caption`

### media_usages

`media_id`, `subject_type`, `subject_id`, `field`

حذف Media با usage فعال ممنوع است؛ archive مجاز است.

## Menu

### menu_categories

`id`, `business_id`, `parent_id`, `public_id`, `slug`, `position`, `status`, `is_featured`

### menu_category_translations

`category_id`, `locale`, `name`, `description`, `seo_title`, `seo_description`

### products

`id`, `business_id`, `category_id`, `public_id`, `slug`, `publication_state`, `is_featured`, `is_new`, `is_best_seller`, `position`, `primary_media_id`

`publication_state` یکی از `draft`, `published`, `inactive`, `archived` است و هیچ معنای موجودی/قیمت ندارد.

### product_translations

`product_id`, `locale`, `name`, `description`, `ingredients`, `allergen_notice`

### product_variants

`id`, `product_id`, `code`, `position`, `is_active`

### product_variant_translations

`variant_id`, `locale`, `name`

### add_on_groups / add_ons

گروه دارای min/max selection است و Add-on دارای publication state و translation است. قیمت یا price delta شعبه‌ای در جدول تنظیمات قیمت قرار می‌گیرد.

### product_branch_settings

`id`, `product_id`, `branch_id`, `price_amount`, `availability_state`, `version`, `updated_by`, `updated_at`

- unique: `(product_id, branch_id)`
- `availability_state`: `available` یا `sold_out`
- Product فقط وقتی عمومی است که `publication_state=published` و Branch setting معتبر داشته باشد.
- `sold_out` Product را منتشرشده نگه می‌دارد و فقط امکان انتخاب/خرید آینده را محدود می‌کند.
- قیمت با integer و currency مربوط به Business تفسیر می‌شود.
- scheduling خودکار availability در V1 وجود ندارد و در صورت نیاز آینده با migration/contract جدا اضافه می‌شود.

### product_variant_branch_prices

`variant_id`, `branch_id`, `price_delta_amount`, `is_available`

### add_on_branch_prices

`add_on_id`, `branch_id`, `price_delta_amount`, `is_available`

دو جدول آخر Contract آینده را کامل می‌کنند. اگر Denardi V1 قیمت متفاوت Variant/Add-on ندارد، UI و automation آن‌ها Deferred می‌ماند و فقط migration لازم در زمان نیاز افزوده می‌شود.

## QR و Analytics

### qr_codes

`id`, `business_id`, `branch_id`, `public_id`, `type`, `target_path`, `table_key`, `table_label`, `is_active`, `expires_at`

در V1 مقدار `type` فقط `general_menu` یا `table` است. برای QR نوع `table`، مقدار `table_key` در Business یکتا است. این شناسه در V1 فقط برای Attribution و در Phase 2 برای Order context استفاده می‌شود. نوع `campaign` و فیلدهای آن تا نیازمندی امضاشده Deferred هستند.

### analytics_events

`id`, `business_id`, `branch_id`, `event_name`, `occurred_at`, `locale`, `device_class`, `qr_id`, `subject_type`, `subject_id`, `session_hash`, `metadata_json`

`session_hash` با salt چرخشی ساخته می‌شود و امکان بازیابی IP/User-Agent اصلی را نمی‌دهد. Raw IP ذخیره نمی‌شود.

## عملیات Core

### module_installations

`module_key`, `version`, `schema_version`, `state`, `installed_at`, `updated_at`, `health_json`

در V1 این جدول وضعیت Moduleهای bundled را ثبت می‌کند و evidence وجود runtime package installer نیست.

### audit_logs

`event_id`, `actor_type`, `actor_id`, `action`, `subject_type`, `subject_id`, `before_json`, `after_json`, `ip_prefix`, `user_agent_class`, `created_at`

### backup_records

`public_id`, `type`, `status`, `location`, `checksum`, `encrypted`, `size`, `started_at`, `completed_at`, `expires_at`, `verified_at`

### instances

`instance_id`, `license_id`, `business_id`, `channel`, `core_version`, `schema_version`, `last_health_at`, `metadata_json`

`license_id` و metadata در V1 محلی و informational هستند. هیچ central lookup، enforcement token یا remote license state لازم نیست.

### ai_usage_records

`request_id`, `business_id`, `user_id`, `feature`, `provider`, `model`, `input_units`, `output_units`, `estimated_cost`, `status`, `created_at`

Prompt کامل یا داده شخصی به‌صورت پیش‌فرض در این جدول ذخیره نمی‌شود.

## Constraints و Indexها

- unique: `(business_id, slug)` برای Page/Category/Product در scope مربوط.
- unique: `(entity_id, locale)` برای Translationها، از جمله `(block_id, locale)`.
- index: Analytics روی `(business_id, event_name, occurred_at)`.
- index: Product روی `(business_id, category_id, publication_state, position)`.
- index/unique: Product Branch setting روی `(branch_id, product_id)`.
- foreign keyها در داده عملیاتی فعال‌اند؛ deleteهای حساس restrict هستند.
- enumهای دامنه در code و DB constraint کنترل می‌شوند.

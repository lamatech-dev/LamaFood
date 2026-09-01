# 04 — Module SDK & Lifecycle

## تصمیم اجرایی Denardi V1

این سند Contract و lifecycle هدف Module SDK را حفظ می‌کند، اما **runtime plugin/package installer پیشرفته در Denardi V1 پیاده‌سازی نمی‌شود**.

در V1:

- Menu و QR داخل source/release artifact Application bundle می‌شوند.
- Manifest در build/CI اعتبارسنجی می‌شود.
- فقط Moduleهای allowlist شده موجود در build می‌توانند از config/state کنترل‌شده enable یا disable شوند.
- Migrationها بخشی از release deployment هستند، نه package دانلودشده در runtime.
- Admin هیچ marketplace، upload ZIP، دانلود remote package، dependency solver عمومی، runtime uninstall یا auto-update Module ندارد.
- جدول `module_installations` صرفاً version/schema/state Moduleهای bundled را ثبت می‌کند.

Contractهای پایین برای حفظ boundary و مسیر رشد محصول معتبرند؛ بخش‌های runtime با برچسب Post-V1 تا اثبات نیاز عملیاتی Deferred هستند.

## ساختار ماژول

```text
modules/Menu/
  module.json
  src/
    MenuServiceProvider.php
    Contracts/
    Actions/
    Models/
    Policies/
    Events/
    Listeners/
  database/migrations/
  routes/admin.php
  routes/public.php
  resources/admin/
  resources/views/
  tests/
```

## Manifest

```json
{
  "key": "menu",
  "name": "Digital Menu",
  "version": "1.0.0",
  "schemaVersion": "1.0.0",
  "core": ">=1.0 <2.0",
  "php": ">=8.3",
  "dependencies": {},
  "permissions": ["menu.view", "menu.edit", "menu.publish"],
  "providers": ["Modules\\Menu\\MenuServiceProvider"],
  "healthChecks": ["menu.database", "menu.media"]
}
```

Manifest در V1 در build/CI و هنگام boot registration کنترل‌شده با JSON schema اعتبارسنجی می‌شود. اعتبارسنجی package دانلودشده مربوط به Post-V1 است.

## V1 Bundled Lifecycle

```text
bundled → registered → disabled/enabled
enabled ↔ disabled
release update → migrate → health check → enabled/disabled
```

فعال‌سازی فقط در maintenance/authorized operation انجام می‌شود، dependencyها از manifestهای موجود در همان build بررسی می‌شوند و نتیجه Audit می‌شود.

## Target Runtime Lifecycle — Post-V1 Deferred

```text
available → installing → disabled → enabling → enabled
                       ↘ failed
enabled → disabling → disabled
enabled/disabled → updating → previous state | failed
disabled → uninstalling → removed
```

هر transition lock دارد تا دو عملیات هم‌زمان اجرا نشوند.

## Runtime Install — Post-V1 Deferred

1. verify package signature/checksum
2. compatibility و dependency check
3. disk/database capacity check
4. ایجاد restore point
5. ثبت state=`installing`
6. اجرای migrationها
7. ثبت permission، route، admin navigation و health check
8. state=`disabled`
9. enable صریح یا خودکار طبق Release plan
10. audit نتیجه

## Disable، Uninstall و Purge

- Disable route/UI/jobهای ماژول را متوقف می‌کند، ولی جدول و داده حفظ می‌شوند.
- V1 فقط Disable/Enable برای Moduleهای bundled دارد.
- Runtime Uninstall و Purge برای Denardi V1 Deferred هستند.
- در Contract آینده، Uninstall code registration را حذف می‌کند ولی داده archived می‌ماند؛ Purge عملیات جدا، مخرب و Lamatech-admin-only خواهد بود.
- Module dependency فعال مانع Disable/Uninstall می‌شود.

## Runtime Package Update — Post-V1 Deferred

- update فقط بین versionهای پشتیبانی‌شده انجام می‌شود.
- هر migration باید forward-safe و قابل retry باشد.
- تغییر بزرگ داده با expand/migrate/contract انجام می‌شود.
- Package جدید تا پایان health check به‌عنوان release candidate نگه داشته می‌شود.

## Rollback Classification

- `code-only`: بازگردانی artifact بدون تغییر داده
- `backward-compatible-schema`: بازگشت code با schema جدید مجاز
- `restore-required`: بازگشت فقط با restore point
- `irreversible`: در Pilot/Stable ممنوع مگر Runbook و تأیید صریح

Rollback خودکار DB به‌عنوان فرض عمومی وجود ندارد.

## قراردادهای ارتباطی

- Module فقط Contract عمومی Module دیگر را import می‌کند.
- Event payload نسخه‌دار و backward-compatible است.
- Listenerها idempotency key دارند.
- Provider adapterها timeout، retry policy و normalized error دارند.

## Health و Compatibility

در V1، Release pre-flight حداقل Core/PHP/DB version، extensions، dependency versions، storage، queue و migration state Moduleهای bundled را بررسی می‌کند. Module تا پاس‌شدن health check `enabled` نمی‌شود. Package download pre-flight مربوط به Post-V1 است.

## Test Contract

هر Module باید unit test، feature test برای permission/routes، migration-from-previous test، disable/enable test و health check test داشته باشد.

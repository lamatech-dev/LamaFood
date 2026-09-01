# 00 — تصمیم‌ها و فرض‌های پایه

## تصمیم‌های معماری

| موضوع | تصمیم V1 |
|---|---|
| مدل محصول | Reusable Single-Tenant؛ هر مشتری Deployment و DB مستقل |
| Backend | Laravel، آخرین نسخه پایدار سازگار با PHP 8.3+ |
| Database | MySQL 8 یا MariaDB معادل؛ Production فقط با Migration |
| Public UI | Laravel Blade + component layer و JavaScript حداقلی |
| Admin UI | React + Vite به‌صورت SPA داخل همان Laravel Deployment |
| Authentication | Session cookie امن؛ Sanctum برای API داخلی Admin |
| معماری | Modular Monolith؛ بدون Microservice در V1 |
| زبان | `fa` فارسی RTL پیش‌فرض، `en` انگلیسی LTR، `ar` عربی RTL؛ URL دارای locale prefix |
| مشتری Pilot | Denardi Cafe تنکابن، تک‌شعبه‌ای در V1 |
| PWA | Installable و online-first؛ بدون ویرایش Offline در Admin |
| AI | Contracts و Governance در Core؛ قابلیت عملیاتی AI خارج از معیار تحویل V1 |
| Hosting | VPS مرجع Production؛ Shared Hosting فقط Profile محدود و پس از تست سازگاری |
| Storage | Local private/public disks با امکان مهاجرت به S3-compatible storage |
| Queue | Database queue در V1؛ Redis در Profile پیشرفته |
| Analytics | First-party و حداقل‌گرا؛ بدون ذخیره IP کامل |
| Catalog و Branch | Product در سطح Business تعریف می‌شود؛ قیمت و availability در Product↔Branch settings قرار می‌گیرد |
| Product lifecycle | publication state مستقل از availability است |
| Module delivery | Moduleهای V1 همراه Application bundle می‌شوند؛ runtime installer/marketplace پیاده‌سازی نمی‌شود |
| QR | General Menu QR و Table QR در V1؛ Campaign QR خارج از Scope مگر قرارداد امضاشده |
| License | فقط Instance/License metadata محلی؛ بدون license server یا enforcement مرکزی در V1 |
| Lamatech access | حساب instance-level با identity `Godfather`، خارج از Business و دارای Gate bypass رسمی؛ بدون role قابل‌نمایش مشتری |

## تصمیم‌های Scope

- V1 یک Business و یک Branch فعال دارد، اما داده‌های عملیاتی کلیدی `branch_id` دارند.
- Ordering، Payment، Reservation، CRM، Loyalty، Inventory، SMS و Push در V1 پیاده‌سازی نمی‌شوند.
- V1 برای هر میز QR قابل‌ردیابی تولید می‌کند، ولی شماره میز فقط در Analytics استفاده می‌شود؛ Ordering در Phase 2 است.
- جست‌وجوی منو و navigation افقی دسته‌ها جزو Scope قطعی V1 هستند.
- Theme Denardi بر پایه Charcoal + Teal + Electric Blue و هویت Art · Coffee · Juice ساخته می‌شود.
- Product یک Catalog record مشترک Business است و با تفاوت قیمت/موجودی شعبه duplicate نمی‌شود.
- Product publication state یکی از `draft/published/inactive/archived` و Branch availability یکی از `available/sold_out` است.
- Module SDK contracts حفظ می‌شوند، اما V1 فقط bundled module registration و enable/disable کنترل‌شده دارد.
- Campaign QR تا ارائه نیازمندی امضاشده Denardi Deferred است.
- Backup معمولی `.env`، APP key یا plaintext secret را بسته‌بندی نمی‌کند؛ secret recovery مسیر جدا و رمزنگاری‌شده دارد.
- License metadata صرفاً شناسنامه و آمادگی آینده است و هیچ remote check یا قطع سرویس خودکاری در V1 ندارد.
- Locale و direction از metadata/config خوانده می‌شوند؛ هیچ شرط Persian-only برای RTL مجاز نیست.
- محتوای عمومی لازم Denardi پیش از Go-live باید برای هر سه locale `fa/en/ar` آماده باشد؛ fallback عمومی خاموش است مگر policy صریح صفحه خلاف آن را تعریف کند.
- Restore از UI فقط برای Lamatech Super Admin روی محیط پشتیبانی‌شده مجاز است؛ CLI همیشه مسیر مرجع است.
- Godfather در هیچ Users/Staff/Roles/Permissions list، count، search یا Business user-management API/UI ظاهر نمی‌شود و فقط Lamatech با فرمان مبتنی بر env آن را bootstrap/rotate می‌کند.
- Public website در حالت اختلال Admin و Queue باید همچنان منوی منتشرشده را نمایش دهد.
- انتشار محتوا Draft/Published است؛ Preview فقط برای کاربران مجاز قابل مشاهده است.

## اصول غیرقابل مذاکره

- هیچ Secret یا Credential در Repository ذخیره نمی‌شود.
- هیچ Migration مخرب بدون Backup، dry-run منطقی و Runbook اجرا نمی‌شود.
- AI به SQL، فایل سیستم یا Provider credentials دسترسی مستقیم ندارد.
- قیمت و موجودی از داده رسمی خوانده می‌شوند؛ AI حق حدس ندارد.
- Module غیرفعال‌شده داده را حذف نمی‌کند.
- حذف داده Module فقط با عملیات جداگانه Purge و تأیید صریح انجام می‌شود.
- سایت عمومی به‌دلیل پایان License خاموش نمی‌شود.

## Infrastructure-ready, Feature-light — Deferral Matrix

| حوزه | Foundation موجود در V1 | Automation/Feature Deferred |
|---|---|---|
| Modules | boundary، manifest، version/state و bundled enable/disable | runtime installer، marketplace، package download، auto-update/uninstall |
| Fleet | instance ID، version metadata و local health | Control Plane، fleet dashboard و central monitoring |
| Updates | release metadata، migrations، backup و rollback runbook | Remote Update Server و staged fleet rollout automation |
| License | local informational metadata | central license server، activation و enforcement |
| AI | contracts، permissions، audit/usage schema و feature flag | provider integration، assistant UI، tools و automated actions |
| QR | General Menu و Table QR | Campaign QR تا signed requirement |
| Multi-branch | Business catalog + Product↔Branch settings | multi-branch management UI و cross-branch reporting |
| Variant/Add-on pricing | قرارداد Branch pricing | UI/migration جزئی فقط اگر منوی تأییدشده Denardi نیاز داشته باشد |

هر مورد Deferred باید بدون route، UI، worker یا سرویس runtime در V1 باقی بماند؛ وجود Contract به‌معنی مجوز پیاده‌سازی Automation نیست.

## موارد تجاری باز

- `TBD-BUSINESS`: قیمت Setup، License سالانه و Moduleها
- `TBD-BUSINESS`: SLA هر Plan
- `TBD-BUSINESS`: مدت نگهداری Backup و Audit Log در هر Plan
- `TBD-BUSINESS`: سقف Storage، Analytics و AI Usage
- `TBD-BUSINESS`: مالکیت Theme کاملاً اختصاصی مشتری

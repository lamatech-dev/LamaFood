# 10 — Commercial, License & Operations Specification

## مالکیت

### متعلق به مشتری

- Domain در صورت خرید به نام مشتری
- محتوای تجاری، منو، قیمت، داده مشتری و Media بارگذاری‌شده
- لوگو و Brand assets متعلق به Denardi
- Export استاندارد داده‌های متعلق به مشتری

### متعلق به Lamatech

- Core، CMS engine، Module SDK و Module code
- Design System foundation و ابزارهای Build/Deployment
- Update، Backup orchestration، Control Plane و AI Gateway
- componentها و utilityهای عمومی قابل استفاده مجدد

Theme اختصاصی و source تحویلی آن باید در قرارداد نهایی صریح شود (`TBD-BUSINESS`).

## اجزای قیمت‌گذاری

### Implementation Fee

Discovery، طراحی Theme، Setup، ورود محتوای اولیه، منو، QR، QA، Deployment و آموزش.

### Annual Platform/Care License

Security/Core update، compatibility، monitoring پایه، backup oversight، support و دسترسی به Updateها.

### Module License/Upsell

Ordering، Payment، Reservation، CRM، Loyalty، Inventory، SMS، Advanced Analytics و AI.

### Managed Services

SEO، محتوا، ورود/بهینه‌سازی منو، Campaign، گزارش Analytics و پشتیبانی مدیریت‌شده.

مبلغ و مالیات هر مورد `TBD-BUSINESS` است و باید پیش از ارسال قرارداد تکمیل شود.

## رفتار License

این بخش policy تجاری آینده را تعریف می‌کند، نه قابلیت اجرایی Denardi V1.

- V1 فقط `instance_id` و `license_id`/contract metadata محلی و قابل مشاهده برای Lamatech دارد.
- در V1 هیچ central license server، activation API، heartbeat، remote enforcement یا automatic expiry action ساخته نمی‌شود.
- پایان قرارداد در V1 یک فرآیند عملیاتی/دستی طبق قرارداد است؛ Application بر اساس metadata محلی چیزی را خودکار قطع نمی‌کند.
- انقضای License سایت عمومی و منوی فعلی را خاموش نمی‌کند.
- update، support و Module update می‌توانند متوقف شوند.
- سرویس‌های دارای هزینه جاری مثل AI/SMS/managed monitoring طبق قرارداد suspend می‌شوند.
- grace period و فرآیند اخطار `TBD-BUSINESS` است.
- اگر بعداً License service ساخته شد، request عمومی نباید به availability آن وابسته شود؛ این طراحی Post-V1 است.

## Hosting و Domain

- مالک حساب Domain، Hosting و billing در قرارداد ثبت می‌شود.
- اگر Lamatech مدیریت می‌کند، دسترسی اضطراری و انتقال مالکیت تعریف می‌شود.
- هزینه Domain، Hosting، SMS، Gateway و AI جدا یا در Plan به‌صورت شفاف اعلام می‌شود.

## Support و SLA

پیشنهاد سطح‌بندی:

| سطح | نمونه تعهد |
|---|---|
| Critical | سایت/منو Down یا رخداد امنیتی؛ پاسخ سریع طبق Plan |
| High | مدیریت قیمت/Publish مختل؛ پاسخ همان روز کاری یا طبق Plan |
| Normal | سؤال، تغییر جزئی یا bug غیرمسدودکننده؛ صف عادی |

ساعات پشتیبانی، زمان پاسخ/حل و کانال رسمی `TBD-BUSINESS` هستند. SLA باید response و resolution را جدا تعریف کند.

## Change Request

- هر مورد خارج از `01-denardi-v1-scope.md` Change Request است.
- CR شامل شرح، اثر روی هزینه/زمان، dependency و Acceptance Criteria است.
- شروع CR نیازمند تأیید کتبی طرف مجاز است.
- درخواست شفاهی در پیام‌رسان Scope را تغییر نمی‌دهد مگر ثبت و تأیید شود.

## Data Export و خروج

- در پایان قرارداد، مشتری می‌تواند محتوای متعلق به خود را در JSON/CSV و Media archive دریافت کند.
- credential و داده مشتری پس از تحویل و پایان retention حذف می‌شوند.
- Core و Module source متعلق به Lamatech جزو Export نیست، مگر قرارداد خلاف آن را بگوید.
- هزینه migration/assistance در صورت نیاز جدا تعیین می‌شود.

## Onboarding Operations

1. امضای Scope و تعیین مالک تصمیم
2. دریافت Brand/content/menu checklist
3. تعیین Domain/Hosting ownership
4. ساخت Staging و ورود داده
5. UAT و اصلاحات داخل Scope
6. تأیید کتبی Go-live
7. آموزش Owner و تحویل Runbook ساده
8. شروع دوره Care و ثبت renewal date

## شاخص‌های موفقیت Pilot

- Denardi بتواند منو را بدون توسعه‌دهنده نگهداری کند.
- خطای قیمت/موجودی ناشی از workflow کاهش یابد.
- QR و Menu usage قابل‌اندازه‌گیری باشد.
- Deploy/Backup/Update در مشتری بعدی قابل تکرار باشد.
- زمان Setup مشتری دوم نسبت به Denardi به‌طور محسوس کاهش یابد.

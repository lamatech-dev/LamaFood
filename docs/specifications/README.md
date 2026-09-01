# Lamatech / Denardi — Implementation Specifications

این پوشه Proposalهای محصول را به تصمیم‌ها و قراردادهای قابل پیاده‌سازی تبدیل می‌کند. در صورت تعارض، ترتیب مرجع به شکل زیر است:

1. `01-denardi-v1-scope.md`
2. `02-core-technical-specification.md`
3. اسناد تخصصی شماره 03 تا 10
4. Proposalهای ریشه پروژه
5. فایل مرجع مشتری: `/Users/Apple/Downloads/Denardi_Lamatech_Final_Proposal.html`

## فهرست اسناد

- `../DENARDI_V1_TRACKER.md` — وضعیت اجرایی، درصد پیشرفت، Release Gateها و کارهای باقی‌مانده
- `../DENARDI_V1_TRACKER.html` — داشبورد تصویری مستقل و فیلترپذیر همان Tracker
- `00-decisions-and-assumptions.md` — تصمیم‌های قطعی و فرض‌های پایه
- `00-denardi-client-requirements.md` — استخراج نیازمندی‌های Proposal تحویلی به Denardi
- `01-denardi-v1-scope.md` — محدوده، خروجی و معیار پذیرش V1
- `02-core-technical-specification.md` — معماری و قراردادهای Core
- `03-domain-data-model.md` — مدل دامنه، جداول و قواعد داده
- `04-module-sdk-lifecycle.md` — قرارداد و چرخه عمر ماژول
- `05-api-specification.md` — استاندارد API و Endpointهای V1
- `06-security-ai-governance.md` — امنیت، حریم خصوصی و کنترل AI
- `07-deployment-backup-release.md` — استقرار، انتشار، بکاپ و بازیابی
- `08-ux-content-seo.md` — جریان‌های UX، محتوا، زبان و SEO
- `09-qa-test-strategy.md` — تست، QA و Release Gate
- `10-commercial-license-operations.md` — مدل لایسنس، خدمات و عملیات
- `FINAL_IMPLEMENTATION_READINESS_REPORT.md` — نتیجه نهایی آمادگی پیش از کدنویسی

## وضعیت تصمیم‌ها

مقادیر دارای برچسب `TBD-BUSINESS` نیازمند تصمیم تجاری Lamatech هستند. این موارد مانع توسعه Foundation نیستند، اما پیش از قرارداد مشتری یا Production باید نهایی شوند.

## Definition of Ready برای شروع کدنویسی

یک Story فقط زمانی Ready است که ورودی/خروجی، Permission، Validation، حالت خطا، رفتار localeهای فعال و Acceptance Test آن مشخص باشد. Denardi V1 دارای `fa/en/ar` است.

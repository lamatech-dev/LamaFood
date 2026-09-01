# Lamatech Modular Restaurant & Cafe Platform
## پروپوزال نهایی معماری محصول — نسخه AI-Ready

**Prepared by Lamatech**
**Version 1.1 — September 2026**

---

# 1. تعریف محصول

Lamatech در حال ساخت یک پلتفرم وب ماژولار اختصاصی برای **کافه‌ها و رستوران‌ها** است؛ نه یک QR Menu ساده، نه WordPress و نه یک SaaS چندمستاجری عمومی.

هر مشتری یک خروجی مستقل دارد:

- دامنه مستقل
- Deployment مستقل
- دیتابیس مستقل
- فایل‌ها و Media مستقل
- Theme و هویت بصری مستقل
- ماژول‌های فعال مخصوص خودش
- مسیر Upgrade مستقل

اما همه پروژه‌ها از یک دارایی مشترک Lamatech ساخته می‌شوند:

```text
Lamatech Core
+ Lamatech Design System
+ Lamatech Module SDK
+ Lamatech Module Library
+ Lamatech AI Layer
+ Deployment / Update / Backup System
```

**Denardi اولین Pilot / Reference Implementation این محصول است.**

---

# 2. مدل معماری

مدل نهایی:

**Reusable Single-Tenant Modular Platform**

```text
Lamatech Product Core
        │
        ├── Denardi Deployment
        ├── Cafe B Deployment
        ├── Restaurant C Deployment
        └── Restaurant D Deployment
```

هر مشتری نصب جدا دارد، ولی Codebase مادر، قراردادهای Core و ماژول‌ها توسط Lamatech نگهداری می‌شوند.

## دلیل انتخاب این مدل

- استقلال کامل داده‌های هر مشتری
- Backup و Restore مستقل
- امنیت و خرابی ایزوله
- امکان شخصی‌سازی واقعی
- فروش ماژول به‌صورت مرحله‌ای
- Upgrade کنترل‌شده
- عدم نیاز به بازنویسی پروژه برای هر مشتری جدید

---

# 3. سه لایه اصلی محصول

## 3.1 Core

قابلیت‌های پایه‌ای که تقریباً در همه پروژه‌ها وجود دارند:

- Business Configuration
- Users / Roles / Permissions
- CMS
- Media Library
- Localization
- SEO Foundation
- Module Manager
- Database Migrations
- Backup / Restore
- Audit Log
- System Health
- Provider Adapters
- Event Layer
- AI Gateway

## 3.2 Theme / Business Configuration

موارد مخصوص هر مشتری:

- Brand Name
- Logo
- Favicon
- Colors
- Typography
- Layout
- Animation Rules
- Contact Data
- Location
- Working Hours
- Social Links
- Languages
- Currency
- Feature Flags
- SEO Defaults

## 3.3 Installable Modules

قابلیت‌هایی که جدا نصب و فعال می‌شوند:

- Digital Menu
- QR Engine
- Ordering
- Table System
- Iranian Payment
- Reservation
- Loyalty
- CRM
- Inventory
- Coupons & Campaigns
- Analytics
- Notifications
- AI Customer Assistant
- AI Manager Copilot
- AI Content Assistant
- AI Analytics Assistant

---

# 4. Core Platform

## 4.1 Business Configuration

مدیریت:

- نام کسب‌وکار
- لوگو و Favicon
- تلفن
- آدرس
- مختصات
- ساعت کاری
- شبکه‌های اجتماعی
- زبان‌ها
- واحد پول
- تنظیمات Domain
- Theme
- Feature Flags
- اطلاعات شعب در نسخه‌های آینده

---

# 5. کاربران و دسترسی‌ها

Role-Based Access Control از ابتدا در Core وجود دارد.

Roleهای نمونه:

- Lamatech Super Admin
- Business Owner
- Manager
- Content Editor
- Cashier
- Staff

Permissionهای نمونه:

```text
menu.read
menu.edit
menu.publish

orders.view
orders.update
orders.cancel

payments.view

customers.view
customers.manage

analytics.view

ai.ask
ai.execute
ai.configure

settings.manage
modules.manage
backups.restore
```

---

# 6. CMS اختصاصی Lamatech

CMS داخل خود محصول ساخته می‌شود و WordPress نیست.

مدل:

**Controlled Block-Based CMS**

Blockهای استاندارد:

- Hero
- About
- Gallery
- Menu Preview
- Products
- Offers
- Reviews
- FAQ
- Location
- Contact
- Instagram
- CTA
- Footer

Owner می‌تواند:

- Block را روشن/خاموش کند
- ترتیب Blockها را تغییر دهد
- متن و تصویر را ویرایش کند
- Variant محدود یک Block را انتخاب کند

اما ساختار Design System کنترل‌شده باقی می‌ماند.

---

# 7. Design System

هدف Lamatech ساخت Template ارزان نیست.

هر مشتری Theme اختصاصی روی Design System مشترک دارد.

```text
Colors
Typography
Spacing
Border Radius
Shadows
Cards
Buttons
Navigation
Sections
Motion
Responsive Rules
Dark / Light Rules
RTL / LTR
```

Laravel هیچ محدودیتی برای زیبایی Frontend ایجاد نمی‌کند.

خروجی می‌تواند:

- Premium
- Responsive
- Animated
- Mobile-first
- App-like
- SEO-friendly
- RTL-ready

باشد.

---

# 8. Stack فنی پیشنهادی

## Backend
**Laravel**

## Database
**MySQL / MariaDB**

## Public Frontend
SSR-friendly / SEO-first frontend

## Admin
**React + Vite**

## PWA
- Customer PWA
- Admin PWA

## معماری
**Modular Monolith**

در V1 از Microservice استفاده نمی‌شود.

---

# 9. Database Architecture

هر مشتری دیتابیس مستقل دارد.

```text
denardi_database
cafe_b_database
restaurant_c_database
```

تمام تغییر Schema فقط از طریق Migration انجام می‌شود.

هیچ تغییر Production Database نباید دستی انجام شود.

---

# 10. Database Migration & Upgrade

هر نسخه Core و هر Module Migration خودش را دارد.

```text
Core 1.0.0
Menu 1.0.0
Menu 1.1.0
QR 1.0.0
Ordering 1.0.0
Payments 1.0.0
```

Upgrade Workflow:

```text
Compatibility Check
        ↓
Create Restore Point
        ↓
Maintenance Mode
        ↓
Run Migrations
        ↓
Update Code
        ↓
Health Check
        ↓
Enable Application
```

---

# 11. Backup & Restore

Backup بخشی از Core است.

## Database Backup
روزانه یا بر اساس Plan.

## Full Backup
Database + Uploads + Config.

## Pre-Update Restore Point

قبل از:

- Core Update
- Module Install
- Module Update
- Module Uninstall
- Database Migration

Restore Point خودکار ایجاد می‌شود.

## Restore Features

- نمایش Backupها
- Download
- Restore
- Rollback آخرین Update
- Maintenance Mode
- ثبت عملیات در Audit Log

---

# 12. Module Manager

هر Module یک Manifest دارد.

```text
Module: Ordering
Version: 1.3.0

Core Requirement:
>= 1.5

Dependencies:
Menu >= 1.2

Migrations:
3

Permissions:
orders.view
orders.manage
orders.cancel
```

## Module Lifecycle

```text
Available
↓
Pre-flight Check
↓
Backup
↓
Install
↓
Migration
↓
Register Permissions
↓
Register Routes
↓
Register Admin Screens
↓
Activate
↓
Health Check
```

همچنین:

```text
Disable
Update
Rollback
Uninstall
```

---

# 13. Event Layer

از ابتدا یک Event Layer ساده داخل Core قرار می‌گیرد.

مثال:

```text
ProductPriceChanged
OrderCreated
OrderPaid
CustomerRegistered
ReservationCreated
QrScanned
LowStockDetected
ModuleUpdated
```

ماژول‌ها به‌جای اتصال مستقیم و شکننده، در صورت مناسب بودن از Eventها استفاده می‌کنند.

این Event Layer بعداً برای:

- Notifications
- Analytics
- Loyalty
- CRM
- AI
- Automation

بسیار مهم خواهد بود.

---

# 14. Digital Menu / Catalog

اولین Module اصلی Denardi.

قابلیت‌ها:

- Category
- Subcategory
- Product
- Product Image
- Description
- Price
- Variant
- Add-on
- Availability
- Sold Out
- Featured
- New
- Best Seller
- Sorting
- Multiple Languages

---

# 15. QR Engine

انواع QR:

- General Menu QR
- Table QR
- Campaign QR
- Product QR
- Landing QR

Tracking:

- Scan Count
- Timestamp
- Device
- Language
- Table
- Campaign
- Landing

---

# 16. Online Ordering

Phase بعدی:

- Cart
- Variant
- Add-on
- Table Order
- Takeaway
- Delivery-ready architecture
- Customer Note
- Order Status
- Kitchen Status

---

# 17. Table System

هر میز QR مستقل دارد.

```text
Table 01
Table 02
Table 03
```

مثال:

```text
Order Source = Table 7
```

---

# 18. Iranian Payments

Payment باید Adapter-Based باشد.

```text
PaymentProvider
```

Providerهای احتمالی:

```text
ZarinpalProvider
IDPayProvider
NextPayProvider
CustomBankProvider
```

Ordering Module نباید به Provider خاص وابسته باشد.

---

# 19. SMS & Notification Providers

SMS هم Adapter-Based طراحی می‌شود.

```text
SmsProvider
```

Providerهای احتمالی:

```text
Kavenegar
Melipayamak
FarazSMS
SMS.ir
CustomProvider
```

لایه Notification:

```text
Notification
 ├── SMS
 ├── Email
 ├── Push
 └── In-App
```

کاربردها:

- OTP
- Order Confirmation
- Reservation Reminder
- Loyalty
- Campaign
- Admin Alert

---

# 20. Reservation

- Table Reservation
- Date / Time
- Capacity
- Confirmation
- Cancellation
- Reminder
- Customer History

---

# 21. Loyalty Club

- Customer Account
- Points
- Credit
- Level
- Coupons
- Birthday Reward
- Referral
- Purchase History
- Offers
- Membership QR

---

# 22. CRM

Customer Model نمونه:

```text
Customer
Phone
Last Visit
Total Purchases
Tags
Favourite Products
Loyalty Level
Notes
```

Segment نمونه:

```text
VIP
Inactive
New Customer
High Spender
Frequent Visitor
```

---

# 23. Inventory

Optional Module:

- Product Stock
- Ingredient Stock
- Low Stock Alert
- Stock Movement
- Consumption
- Waste

---

# 24. Coupons & Campaigns

- Discount Code
- Happy Hour
- Birthday Coupon
- First Order
- QR Campaign
- Time-limited Offer

---

# 25. Analytics Foundation

Pilot:

- Page Views
- QR Scans
- Popular Categories
- Popular Products
- Device
- Language

Advanced:

- Conversion
- Average Basket
- Repeat Customer
- Campaign Performance
- Revenue
- Customer Retention

---

# 26. AI-Ready Architecture

AI نباید به‌صورت چند Feature پراکنده و بدون کنترل وارد سیستم شود.

یک لایه مرکزی ایجاد می‌کنیم:

# Lamatech AI Layer

```text
Customer
Manager
Staff
   │
   ▼
AI Experience Layer
   │
   ▼
Lamatech AI Gateway
   │
   ├── Read Tools
   ├── Approved Action Tools
   ├── Knowledge / Retrieval
   ├── Analytics Context
   └── Provider Adapter
           │
           ├── OpenAI
           ├── Other Cloud Model
           └── Future Local Model
```

این لایه اجازه می‌دهد AI در آینده با تمام Moduleها تعامل داشته باشد، بدون اینکه Moduleها به یک مدل خاص وابسته شوند.

---

# 27. AI Gateway

Core یک AI Gateway مشترک دارد.

وظایف:

- انتخاب Model Provider
- مدیریت API Keys
- Quota
- Cost Tracking
- Prompt Versioning
- Context Building
- Tool Registry
- Permissions
- Audit Log
- Safety Rules
- Request / Response logging در سطح مناسب
- Feature Flags

AI Moduleها مستقیم به OpenAI یا Provider دیگر وصل نمی‌شوند؛ همه از Gateway عبور می‌کنند.

---

# 28. AI Provider Adapter

Contract نمونه:

```text
AiProvider
```

بعد:

```text
OpenAIProvider
ProviderB
ProviderC
LocalModelProvider
```

در نتیجه Lamatech به یک Vendor قفل نمی‌شود.

---

# 29. AI Tool Layer

AI نباید مستقیم SQL اجرا کند.

AI فقط Toolهای کنترل‌شده را می‌بیند.

Read Toolهای نمونه:

```text
getMenu()
getProduct()
getTodayOrders()
getQrAnalytics()
getSalesSummary()
getCustomerSegments()
getInventoryStatus()
```

Action Toolهای نمونه:

```text
updateProductPrice()
markProductSoldOut()
createCampaignDraft()
sendCustomerCampaign()
updateProductDescription()
```

---

# 30. Human Approval برای عملیات حساس

اصل مهم:

**AI حق ندارد تغییر مهم را بدون Approval انجام دهد.**

مثال:

مدیر:

> قیمت لاته را ۱۰٪ زیاد کن.

AI:

```text
قیمت فعلی: 250
قیمت پیشنهادی: 275

Affected item:
Latte

Confirm?
```

فقط بعد از تأیید مدیر Action اجرا می‌شود.

عملیات حساس نمونه:

- تغییر قیمت
- ارسال SMS
- ایجاد Campaign
- Refund
- تغییر موجودی
- حذف داده
- انتشار محتوا
- تغییر تنظیمات

---

# 31. AI Customer Assistant

AI در Interaction با مشتری می‌تواند یکی از جذاب‌ترین Moduleهای آینده باشد.

مشتری QR را Scan می‌کند و می‌تواند بپرسد:

- امروز چی پیشنهاد می‌دی؟
- نوشیدنی بدون قهوه چی دارید؟
- یه نوشیدنی خنک و کم‌شیرین می‌خوام.
- از بین این دوتا کدوم بهتره؟
- این آیتم چه موادی داره؟
- برای دو نفر چی پیشنهاد می‌دی؟
- پرفروش‌ترین آیتم امروز چیه؟

AI فقط از **داده زنده و تأییدشده منو** پاسخ می‌دهد.

## محدودیت مهم

AI نباید در مواردی مانند:

- قیمت
- موجودی
- مواد تشکیل‌دهنده
- آلرژن‌ها

اطلاعات حدسی تولید کند.

اگر داده موجود نباشد، باید صریحاً اعلام کند.

---

# 32. AI Menu Recommender

نسخه اولیه حتی بدون Big Data قابل استفاده است.

بر اساس:

- دسته مورد علاقه
- Sweet / Bitter
- Hot / Cold
- Coffee / Non-coffee
- Budget
- Dietary Preferences
- Current Availability

پیشنهاد ارائه می‌دهد.

در نسخه‌های آینده با داده واقعی رفتار مشتری هوشمندتر می‌شود.

---

# 33. AI Manager Copilot

مدیر می‌تواند با سیستم حرف بزند:

> امروز چه خبر بود؟

AI از Analytics و Operations خلاصه می‌سازد:

```text
امروز:
214 بار منو باز شده
71 اسکن QR ثبت شده
لاته بیشترین بازدید را داشته
موهیتو نسبت به هفته قبل 18٪ رشد داشته
```

یا:

> این هفته چه چیزی نیاز به توجه دارد؟

AI می‌تواند موارد قابل توجه را توضیح دهد.

---

# 34. AI Analytics Assistant

به‌جای اینکه مدیر فقط Chart ببیند، AI Chartها را تفسیر می‌کند.

مثال:

> بازدید منوی شما نسبت به هفته قبل ۲۳٪ افزایش یافته،
> اما بازدید دسته دسر تقریباً ثابت مانده است.

در آینده:

- anomaly detection
- trend summary
- demand forecast
- campaign analysis

اضافه می‌شود.

---

# 35. AI Content Assistant

از فازهای اولیه کاربردی است.

قابلیت‌ها:

- تولید Product Description
- بازنویسی متن
- ترجمه فارسی ↔ انگلیسی
- تولید Meta Description
- پیشنهاد Alt Text
- Caption
- FAQ
- پیشنهاد CTA

همیشه Owner قبل از Publish امکان Review دارد.

---

# 36. AI Translation Assistant

برای منوی چندزبانه:

```text
Persian Source
      ↓
AI Draft Translation
      ↓
Human Review
      ↓
Publish
```

ترجمه AI مستقیماً بدون Review به‌عنوان داده رسمی منتشر نمی‌شود.

---

# 37. AI Campaign Assistant

در آینده Manager می‌تواند بگوید:

> مشتری‌هایی که یک ماه است نیامده‌اند پیدا کن و یک کمپین پیشنهاد بده.

AI:

- Segment پیشنهاد می‌کند.
- Message Draft می‌سازد.
- Discount پیشنهاد می‌کند.
- Audience Count نمایش می‌دهد.
- Estimated Cost SMS را نشان می‌دهد.

اما **Send فقط با Approval مدیر انجام می‌شود.**

---

# 38. AI Loyalty Assistant

در Loyalty/CRM:

- تشخیص مشتری‌های در خطر ریزش
- پیشنهاد Reward
- پیشنهاد Coupon
- Birthday Campaign
- VIP recognition
- Customer Segment explanation

---

# 39. AI Inventory / Demand Assistant

این بخش زمانی فعال می‌شود که داده کافی وجود داشته باشد.

در آینده:

- Predict demand
- Low stock warning
- Suggested purchasing
- Waste analysis
- Popular ingredient trends

این Module نباید برای Pilot به‌صورت کامل ساخته شود.

---

# 40. AI Review & Feedback Assistant

AI می‌تواند:

- Feedbackها را خلاصه کند.
- موضوعات تکرارشونده را پیدا کند.
- Positive / Negative themes را گزارش کند.
- Draft پاسخ آماده کند.

---

# 41. AI Persona / Character Layer

در نسخه پیشرفته‌تر می‌توان برای هر برند Personality تعریف کرد.

مثلاً Denardi Assistant:

- Tone
- Vocabulary
- Greeting Style
- Recommendation Style
- Brand Rules

در آینده حتی Assistant می‌تواند یک Character/Avatar مخصوص برند داشته باشد.

اما Persona فقط Presentation Layer است؛ داده و Actionها همچنان از AI Gateway کنترل می‌شوند.

---

# 42. AI Data & Privacy Rules

از ابتدا باید این قواعد وجود داشته باشد:

- AI فقط داده‌ای را ببیند که Permission دارد.
- اطلاعات حساس مشتری فقط در صورت نیاز وارد Context شود.
- تمام Actionهای AI Audit شوند.
- Prompt و Tool Version ثبت شود.
- Provider-specific data sharing قابل کنترل باشد.
- قابلیت Disable کردن AI برای هر مشتری وجود داشته باشد.
- Quota و Monthly Usage قابل تعریف باشد.

---

# 43. AI Cost Control

چون AI هزینه جاری دارد، از ابتدا باید Usage Metering داشته باشیم.

```text
AI Requests
Tokens / Usage
Estimated Cost
Module
Business
User
Date
```

در آینده این امکان می‌دهد Lamatech پلن‌های AI جدا بفروشد.

مثلاً:

```text
AI Basic
AI Business
AI Pro
```

---

# 44. AI Read vs AI Action

دو سطح جدا:

## AI Read
فقط سؤال و تحلیل.

ریسک پایین‌تر.

مثال:

- امروز چند QR Scan داشتیم؟
- کدام محصول بیشتر دیده شد؟
- چه آیتمی برای مشتری پیشنهاد می‌دی؟

## AI Action
تغییر سیستم.

مثال:

- قیمت را تغییر بده.
- SMS ارسال کن.
- کمپین فعال کن.
- محصول را Sold Out کن.

AI Action باید Permission + Confirmation + Audit داشته باشد.

---

# 45. PWA مشتری

- Web App Manifest
- Icons
- Installability
- Cache
- Offline Shell
- Responsive
- Mobile-first

---

# 46. Admin PWA

صاحب کافه با گوشی:

- محصول اضافه می‌کند.
- قیمت تغییر می‌دهد.
- عکس اضافه می‌کند.
- Sold Out می‌کند.
- محتوا مدیریت می‌کند.
- سفارش می‌بیند.
- رزرو می‌بیند.
- Analytics می‌بیند.
- با AI Copilot گفتگو می‌کند.

---

# 47. Native App

فعلاً ضروری نیست.

Backend و API باید آماده باشند تا بعدها:

- Flutter
- React Native
- Native Android
- Native iOS

ساخته شود.

---

# 48. SEO Engine

Lamatech فقط Menu نمی‌فروشد؛ **Digital Presence** می‌فروشد.

هر سایت:

- Semantic HTML
- Mobile Optimization
- Meta Title / Description
- Canonical
- OpenGraph
- Sitemap
- robots.txt
- Image Alt
- Clean URLs
- LocalBusiness Schema
- Restaurant / Cafe Schema
- Location Data

خواهد داشت.

---

# 49. Localization

Core از روز اول Multilingual است.

Pilot Denardi:

```text
FA
EN
```

بعداً:

```text
AR
RU
TR
...
```

---

# 50. Media Library

- Upload
- Resize
- Crop
- Thumbnail
- WebP
- AVIF
- Alt
- Title
- Caption
- File Size
- Reuse
- Delete

---

# 51. Security

- CSRF
- Rate Limiting
- Password Hashing
- Secure Sessions
- Permission Checks
- Upload Validation
- MIME Validation
- Audit Log
- Login Protection
- Environment Secrets
- Secure Headers
- Input Validation

---

# 52. Audit Log

مثال:

```text
Manager Ali
changed Cappuccino price
230 → 250
```

یا:

```text
AI Manager Copilot
requested price change:
250 → 275

Approved by:
Business Owner
```

یا:

```text
Lamatech Admin
installed Ordering 1.1.0
```

---

# 53. System Health

پنل Lamatech:

```text
Core Version
PHP Version
Database Version
Storage Usage
Last Backup
Cron Status
Module Versions
Queue Status
AI Gateway Status
AI Usage
Error Logs
SSL
Disk Space
```

---

# 54. Versioning

Core:

```text
1.0.0
1.1.0
1.2.0
```

Modules:

```text
Menu 1.4.0
QR 1.2.0
Ordering 1.0.0
AI Customer Assistant 0.5.0
AI Manager Copilot 0.3.0
```

---

# 55. Deployment Profiles

## Profile A — Shared Hosting

مناسب:

- Landing
- CMS
- Menu
- QR
- Basic Analytics

AI می‌تواند از API Provider بیرونی استفاده کند، مشروط به امکان HTTP Outbound و تنظیم امن Keys.

## Profile B — VPS

مناسب:

- Ordering
- Payments
- SMS
- Loyalty
- CRM
- Queue
- AI Jobs

## Profile C — Advanced

- Redis
- CDN
- Object Storage
- Advanced Queue
- Monitoring
- Higher AI workloads
- Multi-branch integrations

---

# 56. Denardi Pilot

برای Denardi کل Roadmap ساخته نمی‌شود.

## Denardi V1 — ON

- Core
- Business Configuration
- Denardi Theme
- Landing Page
- CMS
- Media
- Digital Menu
- QR
- FA / EN
- SEO
- Basic Analytics
- Admin PWA
- Users / Roles
- Backup
- Audit Base
- Event Layer Base
- AI-ready Contracts

## AI در V1

حداقل زیرساخت AI:

- AiProvider Contract
- AI Gateway Skeleton
- Tool Registry Contract
- Permission Model
- Audit Model
- Feature Flag
- Usage Model

قابلیت‌های AI کامل الزام Denardi V1 نیستند.

در صورت نیاز می‌توان یک Feature کوچک و قابل نمایش اضافه کرد:

**AI Menu Assistant / Recommendation Beta**

---

# 57. Modules Compatible but OFF

- Ordering
- Payment
- Reservation
- Loyalty
- CRM
- Inventory
- Coupons
- SMS / Push
- Advanced Analytics
- AI Manager Copilot
- AI Campaign Assistant
- AI Demand Forecast
- AI Loyalty Assistant

---

# 58. اصل توسعه Denardi

برای Pilot:

**Infrastructure درست، Feature Set کوچک.**

نه کل محصول را قبل از فروش می‌سازیم و نه Throwaway Code تولید می‌کنیم.

```text
Build what Denardi needs now
+
Keep contracts ready for what Lamatech sells later
```

---

# 59. Roadmap پیشنهادی

## Phase 0 — Product Foundation

- Repository
- Laravel
- MySQL
- Core Module Contract
- Business Config
- Users / Roles
- Theme Contract
- Event Contract
- Base API

## Phase 1 — Denardi Core

- CMS
- Media
- SEO
- Localization
- Menu
- QR
- Basic Analytics
- Admin PWA

## Phase 1.5 — Reliability

- Backup
- Restore
- Audit
- Module Versioning
- Health
- Update-safe migrations

## Phase 2 — Commerce

- Ordering
- Table System
- Iranian Payment
- SMS / Notifications

## Phase 3 — Customer Growth

- CRM
- Loyalty
- Coupons
- Reservation
- Advanced Analytics

## Phase 4 — AI Experience

- AI Customer Assistant
- AI Menu Recommender
- AI Content Assistant
- AI Manager Copilot

## Phase 5 — AI Operations

- AI Campaign Assistant
- AI CRM Insights
- AI Loyalty Suggestions
- Forecasting
- Inventory Intelligence
- Automated Business Insights

---

# 60. مدل درآمدی آینده Lamatech

محصول می‌تواند لایه‌ای فروخته شود.

## Base
- Website
- CMS
- SEO
- PWA

## Menu Pack
- Digital Menu
- QR
- Basic Analytics

## Commerce Pack
- Ordering
- Payment
- Table System

## Customer Pack
- CRM
- Loyalty
- Reservation
- Campaigns

## AI Pack
- AI Customer Assistant
- AI Content
- AI Manager Copilot
- AI Analytics

## AI Pro
- CRM Intelligence
- Campaign Automation
- Forecasting
- Inventory Intelligence

این ساختار درآمد را از «فروش یک‌باره سایت» به **Recurring Upgrade / Module Revenue** تبدیل می‌کند.

---

# 61. Asset واقعی Lamatech

دارایی اصلی Lamatech:

```text
Lamatech Core
+
Lamatech Design System
+
Lamatech Module SDK
+
Lamatech Module Library
+
Lamatech AI Gateway
+
Lamatech AI Tool Contracts
+
Lamatech Provider Adapters
+
Lamatech Deployment System
+
Lamatech Update / Backup System
```

Denardi اولین اثبات عملی این Asset خواهد بود.

---

# 62. تصمیم نهایی معماری

> **Laravel Modular Monolith + MySQL/MariaDB + SEO-first Public Frontend + React-based Admin PWA + Module Contracts + Event Layer + Database Migrations + Automated Backup/Restore + Payment/SMS/AI Provider Adapters + AI Gateway + Independent Deployment per Customer**

اصل نهایی:

> **Denardi را سریع می‌سازیم، اما هیچ بخش پایه‌ای آن Throwaway Code نخواهد بود. AI را هم از روز اول به‌صورت Architecture-ready در نظر می‌گیریم، نه اینکه تمام قابلیت‌های AI را قبل از نیاز واقعی پیاده‌سازی کنیم.**

---

# 63. سند بعدی

بعد از این Proposal:

## Lamatech Core Technical Specification v1.0

باید شامل:

- Folder Structure
- Database Schema
- Core Contracts
- Module Manifest
- Module Lifecycle
- Event Contracts
- API Conventions
- Authentication
- Permission Model
- Migration Rules
- Backup Lifecycle
- Restore Workflow
- Theme Contract
- CMS Block Contract
- Payment Adapter
- SMS Adapter
- AI Provider Contract
- AI Gateway
- AI Tool Contract
- AI Approval Workflow
- AI Usage Metering
- PWA Structure
- SEO Contract
- Deployment Checklist
- Denardi Acceptance Criteria
- QA Checklist
- Versioning
- Release Process

باشد.

---

**Prepared by Lamatech**
**Restaurant & Cafe Modular Business Platform — AI-Ready Product Architecture**

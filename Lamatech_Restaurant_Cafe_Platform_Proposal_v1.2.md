# Lamatech Restaurant & Cafe Platform
## پروپوزال نهایی محصول، زیرساخت مقیاس‌پذیر و مدل تجاری — نسخه 1.2

**Prepared by Lamatech**
**Product Architecture + Operations + Commercial Model**
**September 2026**

---

# 1. تعریف محصول

Lamatech در حال ساخت یک پلتفرم اختصاصی برای **کافه‌ها و رستوران‌ها** است که به هر کسب‌وکار یک هویت دیجیتال کامل می‌دهد؛ نه صرفاً یک سایت و نه صرفاً یک QR Menu.

خروجی برای هر مشتری می‌تواند شامل این موارد باشد:

- وب‌سایت اختصاصی و Responsive
- Landing Page حرفه‌ای
- منوی دیجیتال
- QR
- مدیریت قیمت و آیتم
- پنل مدیریت موبایل
- SEO پایه و Local SEO
- Analytics
- PWA
- سفارش آنلاین
- پرداخت ریالی
- رزرو
- CRM
- باشگاه مشتریان
- SMS / Push
- AI Customer Assistant
- AI Manager Copilot

**Denardi اولین Pilot / Reference Implementation این محصول است.**

---

# 2. هدف تجاری Lamatech

هدف این نیست که Lamatech برای هر کافه یک پروژه کاملاً جدا و دستی بسازد.

هدف:

> ساخت یک Product Core مشترک که بتوان آن را بارها فروخت، سریع Deploy کرد، از راه دور مدیریت کرد، Upgrade کرد و در طول زمان Moduleهای جدید به هر مشتری فروخت.

این مدل باید بتواند از:

```text
5 مشتری
↓
20 مشتری
↓
50 مشتری
↓
100+ مشتری
```

رشد کند، بدون اینکه هزینه پشتیبانی به همان نسبت افزایش پیدا کند.

---

# 3. مدل معماری

مدل نهایی:

**Reusable Single-Tenant Modular Platform**

هر مشتری:

- دامنه مستقل دارد.
- Deployment مستقل دارد.
- دیتابیس مستقل دارد.
- Storage مستقل دارد.
- Theme مستقل دارد.
- Config مستقل دارد.
- Moduleهای فعال مستقل دارد.

اما همه از یک دارایی مشترک Lamatech ساخته می‌شوند:

```text
Lamatech Core
+
Lamatech Design System
+
Lamatech Module SDK
+
Lamatech Module Library
+
Lamatech AI Layer
+
Lamatech Deployment System
+
Lamatech Update System
+
Lamatech Control Plane
```

---

# 4. مالکیت و مدل License پیشنهادی

مدل پیشنهادی برای جلوگیری از ابهام:

## متعلق به مشتری

- Domain
- Business Content
- Product/Menu Data
- Customer Data
- Uploaded Media
- Branding
- Business-specific configuration

## متعلق به Lamatech

- Core Software
- CMS Engine
- Module System
- Module Code
- Update System
- Design System foundation
- AI Gateway
- Control Plane
- Deployment tooling

یعنی مشتری یک سایت عملیاتی مستقل دریافت می‌کند، ولی **هسته نرم‌افزاری Lamatech تحت License استفاده می‌شود**.

این مدل اجازه می‌دهد Lamatech:

- Update ارائه کند.
- Module جدید بفروشد.
- Maintenance ارائه کند.
- Security patch ارائه کند.
- محصول را برای صدها مشتری دیگر نیز استفاده کند.

---

# 5. مدل درآمدی پیشنهادی

مدل درآمدی بهتر است چندلایه باشد.

## 5.1 Implementation Fee

هزینه اولیه:

- Setup
- Design
- Theme
- Content Entry
- Deployment
- Initial SEO
- Menu Setup
- QR

## 5.2 Annual Platform / Care License

هزینه سالانه برای:

- Core Updates
- Security Updates
- Compatibility
- Backup Monitoring
- Health Monitoring
- Support
- Update Access

## 5.3 Module Upsell

فروش جداگانه:

- Ordering
- Payment
- Reservation
- CRM
- Loyalty
- Inventory
- SMS
- AI
- Advanced Analytics

## 5.4 Managed Services

خدمات ماهانه:

- SEO
- Content
- Menu Optimization
- Campaign Management
- Analytics Review
- AI Usage
- Managed Support

این ساختار درآمد Lamatech را از **فروش یک‌باره سایت** به **Recurring Revenue + Module Revenue** تبدیل می‌کند.

---

# 6. اصل کلیدی مقیاس‌پذیری

اگر Lamatech به 100 مشتری برسد، نباید 100 سایت را به‌صورت دستی نگهداری کند.

بنابراین از ابتدا دو بخش در معماری دیده می‌شود:

## Customer Instance

سایت مستقل مشتری.

## Lamatech Control Plane

پنل مرکزی Lamatech برای مدیریت کل Fleet.

```text
                    Lamatech Control Plane
                           │
       ┌───────────────────┼───────────────────┐
       │                   │                   │
   Denardi             Cafe B             Restaurant C
   Instance            Instance              Instance
```

---

# 7. Lamatech Control Plane

این بخش یکی از مهم‌ترین دارایی‌های آینده محصول است.

Control Plane باید در فازهای بعدی بتواند تمام Instanceهای فعال را نمایش دهد.

برای هر مشتری:

- Business Name
- Domain
- Core Version
- Module Versions
- Active Modules
- PHP Version
- Database Version
- Last Backup
- Last Health Check
- SSL Status
- Disk Usage
- Error Status
- License Status
- Last Update
- Available Updates
- AI Usage
- Support Notes

نمای کلی:

```text
Customers: 54
Healthy: 50
Attention: 3
Critical: 1

Core 1.4:
32 instances

Core 1.3:
18 instances

Updates Available:
22 instances
```

---

# 8. وضعیت Control Plane در V1

Control Plane کامل برای Denardi ساخته نمی‌شود.

اما Core باید از ابتدا این اطلاعات را قابل استخراج کند:

- Instance ID
- Core Version
- Module Versions
- Health Endpoint
- License ID
- Update Channel
- Backup Status
- Environment Info

یعنی **Hookهای لازم از ابتدا وجود دارند**، ولی Dashboard مرکزی وقتی Market Validation انجام شد ساخته می‌شود.

---

# 9. Rollout Strategy

برای کاهش ریسک:

## مرحله اول

Denardi:

- Pilot
- Architecture validation
- UX validation
- Deployment validation

## مرحله دوم

5 تا 6 مشتری اول:

- Install جدا
- دریافت Feedback
- رفع مشکلات Deployment
- بهبود Module contracts
- تست Update واقعی
- تست Backup/Restore
- کشف نیازهای مشترک

در این مرحله در صورت نیاز نسخه‌های جدید را حتی به‌صورت Controlled Replacement روی مشتری‌های اولیه اعمال می‌کنیم.

## مرحله سوم

بعد از اثبات بازار:

- Control Plane
- Remote Update System
- Central Monitoring
- License Manager
- Fleet Backup Status
- Automated Deployment

---

# 10. Core Platform

Core شامل:

- Business Configuration
- Users
- Roles
- Permissions
- CMS
- Media Library
- Localization
- SEO
- Database Layer
- Module Manager
- Event Layer
- Backup / Restore
- Audit Log
- Health System
- Provider Adapters
- AI Gateway Skeleton
- Instance Identity
- Update Metadata

---

# 11. Business Configuration

- Name
- Logo
- Favicon
- Contact
- Location
- Working Hours
- Social Links
- Languages
- Currency
- Domain
- Theme
- Feature Flags
- Branch-ready metadata

---

# 12. Design System

ظاهر محصول باید یکی از نقاط فروش Lamatech باشد.

هر مشتری Theme خودش را دارد، اما روی Design System مشترک.

```text
Colors
Typography
Spacing
Cards
Buttons
Navigation
Sections
Motion
Responsive Rules
RTL / LTR
Dark / Light Rules
```

Backend محدودیتی برای زیبایی ایجاد نمی‌کند.

Frontend باید:

- Premium
- Mobile-first
- Responsive
- Fast
- SEO-friendly
- App-like
- Accessible

باشد.

---

# 13. CMS اختصاصی Lamatech

WordPress استفاده نمی‌شود.

مدل:

**Controlled Block-Based CMS**

Blockهای نمونه:

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
- Social
- CTA
- Footer

Owner می‌تواند:

- محتوا را تغییر دهد.
- Block را روشن/خاموش کند.
- ترتیب را تغییر دهد.
- تصویر را عوض کند.

ولی Design System خراب نمی‌شود.

---

# 14. Stack فنی

## Backend

**Laravel**

## Database

**MySQL / MariaDB**

## Public Frontend

SEO-first / SSR-friendly Frontend

## Admin

**React + Vite**

## PWA

- Customer PWA
- Admin PWA

## Architecture

**Modular Monolith**

---

# 15. چرا Modular Monolith؟

در فاز فعلی Microservice ضروری نیست.

Modular Monolith:

- ساده‌تر Deploy می‌شود.
- روی Shared Hosting بهتر جواب می‌دهد.
- Backup ساده‌تر است.
- Debug ساده‌تر است.
- Transactionهای دیتابیس ساده‌تر هستند.
- برای ده‌ها و صدها Instance مستقل مناسب است.

در آینده فقط بخش‌هایی که واقعاً نیاز دارند Service جدا می‌شوند.

---

# 16. Database Architecture

هر مشتری Database مستقل دارد.

```text
denardi_database
cafe_b_database
restaurant_c_database
```

تمام تغییرات Schema از طریق Versioned Migration انجام می‌شوند.

---

# 17. Migration Safety

قبل از هر تغییر دیتابیس:

```text
Check Compatibility
↓
Create Restore Point
↓
Enable Maintenance Mode
↓
Run Migration
↓
Run Health Check
↓
Return Online
```

هیچ Update نباید بدون Restore Point وارد Production شود.

---

# 18. Backup Strategy

## Daily Database Backup

دیتابیس.

## Full Backup

Database + Uploads + Config.

## Pre-Update Backup

قبل از:

- Core Update
- Module Install
- Module Update
- Module Removal
- Database Migration

## Retention

قابل تنظیم بر اساس Plan.

---

# 19. Restore Strategy

- One-click Restore برای Lamatech Admin
- Maintenance Mode
- Restore Database
- Restore Files
- Rollback Update
- Audit Log
- Restore Validation

---

# 20. Module Manager

هر Module Manifest دارد.

```text
Module: Ordering
Version: 1.3.0
Core: >=1.5

Dependencies:
Menu >=1.2

Permissions:
orders.view
orders.manage

Migrations:
3
```

---

# 21. Module Lifecycle

```text
Available
↓
Pre-flight
↓
Backup
↓
Install
↓
Migration
↓
Register
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

# 22. Remote Update Architecture

هدف نهایی:

Lamatech Update Server نسخه‌های Core و Module را منتشر کند.

Instance بتواند:

```text
Check Update
↓
Verify Package
↓
Verify Compatibility
↓
Backup
↓
Download
↓
Apply
↓
Migrate
↓
Health Check
↓
Report Result
```

در فازهای اولیه این فرآیند می‌تواند نیمه‌دستی باشد.

بعد از Market Validation، به سیستم Remote Update واقعی تبدیل می‌شود.

---

# 23. Update Channels

برای کاهش ریسک:

```text
Stable
Pilot
Internal
```

مثلاً:

- Denardi روی Pilot Channel
- مشتری‌های عادی روی Stable
- تست Lamatech روی Internal

این اجازه می‌دهد Update ابتدا روی چند Instance محدود تست شود.

---

# 24. Update Rollout

در آینده:

```text
Internal
↓
Denardi
↓
5 Early Customers
↓
10%
↓
50%
↓
100%
```

در صورت خطا Rollout متوقف می‌شود.

---

# 25. Digital Menu Module

- Category
- Subcategory
- Product
- Image
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

# 26. QR Engine

- Menu QR
- Table QR
- Product QR
- Campaign QR
- Landing QR

Tracking:

- Scan Count
- Time
- Table
- Device
- Language
- Campaign

---

# 27. Ordering Module

- Cart
- Add-ons
- Variants
- Table Order
- Takeaway
- Customer Note
- Order Status
- Kitchen Status

---

# 28. Iranian Payment

Adapter-Based:

```text
PaymentProvider
```

Possible adapters:

```text
ZarinpalProvider
IDPayProvider
NextPayProvider
CustomBankProvider
```

---

# 29. SMS Provider Layer

```text
SmsProvider
```

Possible adapters:

```text
Kavenegar
Melipayamak
FarazSMS
SMS.ir
CustomProvider
```

کاربرد:

- OTP
- Order
- Reservation
- Campaign
- Loyalty
- Admin Alert

---

# 30. Notification Layer

```text
Notification
 ├ SMS
 ├ Email
 ├ Push
 └ In-App
```

Moduleها نباید مستقیم به Vendor خاص متصل شوند.

---

# 31. Reservation

- Table
- Date
- Time
- Capacity
- Confirmation
- Reminder
- Cancellation
- Customer History

---

# 32. Loyalty

- Points
- Credit
- Levels
- Coupons
- Referral
- Birthday Reward
- Offers
- Customer History

---

# 33. CRM

- Customer Profile
- Tags
- Last Visit
- Purchase History
- Favourite Products
- Notes
- Segments

نمونه Segment:

```text
VIP
Inactive
New
High Spender
Frequent Visitor
```

---

# 34. Inventory

- Product Stock
- Ingredient Stock
- Low Stock
- Movement
- Consumption
- Waste

Optional Module.

---

# 35. Analytics

Pilot:

- Page Views
- QR Scans
- Popular Categories
- Popular Items
- Device
- Language

Advanced:

- Orders
- Conversion
- Revenue
- Average Basket
- Repeat Customer
- Campaign Performance

---

# 36. Event Layer

Core Eventهای پایه:

```text
ProductPriceChanged
ProductSoldOut
OrderCreated
OrderPaid
CustomerRegistered
QrScanned
ReservationCreated
ModuleUpdated
BackupCompleted
HealthCheckFailed
```

این Event Layer پایه آینده:

- Analytics
- Notification
- Loyalty
- CRM
- Automation
- AI

است.

---

# 37. AI-Ready Architecture

یک لایه مرکزی:

**Lamatech AI Gateway**

```text
Customer / Manager
        │
        ▼
AI Experience
        │
        ▼
Lamatech AI Gateway
        │
        ├── Read Tools
        ├── Approved Actions
        ├── Context Builder
        ├── Usage Meter
        └── Provider Adapter
```

---

# 38. AI Customer Assistant

مشتری از داخل منو می‌تواند بپرسد:

- امروز چی پیشنهاد می‌دی؟
- نوشیدنی خنک و کم‌شیرین چی دارید؟
- بدون قهوه چی دارید؟
- کدام محصول پرفروش‌تر است؟
- این محصول چه ترکیباتی دارد؟

AI فقط از داده تأییدشده پاسخ می‌دهد.

---

# 39. AI Manager Copilot

مدیر می‌تواند بپرسد:

- امروز چند نفر منو را دیدند؟
- کدام محصول بیشتر دیده شد؟
- نسبت به هفته قبل چه تغییری داشتیم؟
- چه چیزی نیاز به توجه دارد؟

بعداً AI می‌تواند Action پیشنهاد دهد.

---

# 40. AI Approved Actions

AI مستقیماً تغییر حساس انجام نمی‌دهد.

مثال:

```text
Current Latte Price:
250

Requested:
+10%

New Price:
275

Waiting for Owner Approval
```

Actionهای حساس:

- Price Change
- SMS Send
- Campaign Activation
- Refund
- Inventory Adjustment
- Publish
- Delete

نیازمند:

- Permission
- Confirmation
- Audit

---

# 41. AI Content Assistant

- Product Description
- Translation
- SEO Description
- Alt Text
- FAQ
- Caption
- CTA

---

# 42. AI Analytics

در آینده:

- Daily Summary
- Trend Detection
- Anomaly Detection
- Campaign Analysis
- Forecasting
- Customer Retention Insights

---

# 43. AI Cost Control

برای هر Instance:

```text
AI Requests
Usage
Estimated Cost
Module
User
Date
```

بعداً Lamatech می‌تواند AI را به‌صورت Plan جدا بفروشد.

---

# 44. PWA

## Customer PWA

- Installable
- Mobile-first
- Cache
- Offline shell
- App Icon

## Admin PWA

صاحب کافه از گوشی:

- قیمت تغییر دهد.
- محصول اضافه کند.
- موجودی تغییر دهد.
- سفارش ببیند.
- محتوا و منو مدیریت کند.
- Analytics ببیند.
- با AI Copilot صحبت کند.

---

# 45. SEO

Lamatech فقط Menu نمی‌فروشد؛ **Digital Presence** می‌فروشد.

- Semantic HTML
- Meta
- Canonical
- OpenGraph
- Sitemap
- robots.txt
- Image SEO
- Clean URLs
- LocalBusiness Schema
- Restaurant/Cafe Schema
- Location Data

---

# 46. Security

- CSRF
- Rate Limiting
- Password Hashing
- Secure Session
- Permission Checks
- Upload Validation
- MIME Validation
- Audit
- Login Protection
- Environment Secrets
- Secure Headers
- Input Validation

---

# 47. Audit Log

نمونه:

```text
Manager
changed Cappuccino price
230 → 250
```

```text
Lamatech Admin
installed Ordering 1.1
```

```text
AI Copilot
requested Price Change

Approved by:
Owner
```

---

# 48. Health Endpoint

هر Instance باید Health Endpoint امن داشته باشد.

اطلاعات قابل گزارش به Control Plane:

```text
Instance ID
Core Version
Module Versions
DB Status
Disk
Backup
Cron
Queue
SSL
Last Error
AI Gateway
```

داده‌های حساس مشتری از این Endpoint ارسال نمی‌شوند.

---

# 49. License / Instance Identity

هر Deployment:

```text
Instance ID
License ID
Business ID
Update Channel
Installed Modules
License Expiry
```

دارد.

این بخش پایه آینده License Manager خواهد بود.

---

# 50. License Behaviour

نباید سایت مشتری با تمام شدن License ناگهان Down شود.

مدل حرفه‌ای:

در صورت اتمام قرارداد:

- سایت عمومی ادامه کار می‌دهد.
- Core Update متوقف می‌شود.
- Remote Support محدود می‌شود.
- Module Update متوقف می‌شود.
- سرویس‌های Cloud/AI وابسته به Plan می‌توانند متوقف شوند.

این رفتار هم از نظر تجاری بهتر است و هم ریسک مشتری را کاهش می‌دهد.

---

# 51. Support Scalability

هدف:

Lamatech به‌جای ورود دستی به هر سایت، از Control Plane بفهمد:

```text
What is broken?
Who needs update?
Who missed backup?
Who has expired SSL?
Who has outdated core?
Who has module errors?
```

به این ترتیب 100 مشتری الزاماً 100 برابر Support ایجاد نمی‌کند.

---

# 52. Deployment Profiles

## Shared Hosting

- Website
- CMS
- Menu
- QR
- Basic Analytics

## VPS

- Ordering
- Payment
- SMS
- CRM
- Loyalty
- Queue
- AI workloads

## Advanced

- Redis
- CDN
- Object Storage
- Monitoring
- Advanced Queue
- Heavy AI
- Multi-branch

---

# 53. Denardi V1

Modules ON:

- Core
- Business Config
- Denardi Theme
- Landing
- CMS
- Media
- Menu
- QR
- FA / EN
- SEO
- Basic Analytics
- Admin PWA
- User / Role
- Backup
- Audit
- Event Layer Base
- Instance Identity
- Update Metadata
- Health Endpoint
- AI Contracts

---

# 54. فعلاً برای Denardi کامل ساخته نمی‌شود

- Central Control Plane
- Automatic Fleet Updates
- License Dashboard
- Ordering
- Payment
- CRM
- Loyalty
- Reservation
- Inventory
- SMS
- Advanced AI
- Advanced Analytics

اما Core باید با آن‌ها سازگار باشد.

---

# 55. Development Principle

> **Infrastructure-ready, feature-light.**

برای Denardi:

- Featureهای مورد نیاز را کامل می‌کنیم.
- Hookهای آینده را صحیح می‌گذاریم.
- سیستم مرکزی سنگین را فعلاً نمی‌سازیم.
- بعد از 5 تا 6 مشتری واقعی تصمیم می‌گیریم چه چیزی Automation شود.

---

# 56. Roadmap

## Phase 0 — Foundation

- Laravel
- MySQL
- Module Contract
- Users/Roles
- Business Config
- Theme
- Events
- Instance Identity
- Update Metadata
- Health Contract

## Phase 1 — Denardi

- CMS
- Media
- Menu
- QR
- SEO
- Localization
- Basic Analytics
- Admin PWA

## Phase 1.5 — Reliability

- Backup
- Restore
- Audit
- Versioning
- Safe Migrations
- Update Preparation

## Phase 2 — Market Validation

- 5–6 Customers
- Real Deployment Testing
- Upgrade Testing
- Common Needs Analysis
- Support Workflow

## Phase 3 — Commerce

- Ordering
- Table
- Payment
- Notifications

## Phase 4 — Customer Growth

- CRM
- Loyalty
- Reservation
- Campaigns

## Phase 5 — Lamatech Operations

- Control Plane
- License Manager
- Fleet Monitoring
- Remote Update
- Update Channels
- Central Health
- Backup Monitoring

## Phase 6 — AI

- Customer Assistant
- Content AI
- Manager Copilot
- Analytics AI
- Campaign AI
- Forecasting

---

# 57. معیار تصمیم برای ساخت Control Plane

Control Plane کامل زمانی ساخته شود که حداقل یکی از این شرایط برقرار باشد:

- مدیریت دستی Update آزاردهنده شده.
- تعداد Instanceها از حدود 5–10 عبور کرده.
- بیش از یک نسخه Core همزمان در Production داریم.
- Backup Monitoring دستی قابل اعتماد نیست.
- Support Time در حال رشد است.
- بازار اولیه Product را تأیید کرده.

---

# 58. مدل Product Pack آینده

## Digital Presence

- Website
- CMS
- SEO
- PWA

## Menu

- Digital Menu
- QR
- Analytics

## Commerce

- Ordering
- Table
- Payment

## Customer Growth

- CRM
- Loyalty
- Reservation
- Campaigns

## AI

- Customer Assistant
- Manager Copilot
- Content
- Analytics

## Managed by Lamatech

- Monitoring
- Updates
- Backup
- SEO
- Content
- Support

---

# 59. دارایی واقعی Lamatech

```text
Lamatech Core
+
Design System
+
Module SDK
+
Module Library
+
AI Gateway
+
Provider Adapters
+
Instance Identity
+
Update Infrastructure
+
Control Plane
+
Deployment Process
+
Support Process
```

هر مشتری جدید باید این Asset را قوی‌تر کند، نه اینکه یک Codebase جدید و جدا ایجاد کند.

---

# 60. تصمیم نهایی

> **Laravel Modular Monolith + MySQL/MariaDB + SEO-first Public Frontend + React Admin PWA + Independent Customer Deployments + Versioned Modules + Safe Migrations + Automated Backup/Restore + Event Layer + Provider Adapters + AI-ready Gateway + Control-Plane-ready Instance Identity.**

و اصل اجرایی:

> **Denardi را سریع و قابل فروش می‌سازیم. زیرساختی که بعداً برای مقیاس لازم است از نظر Contract و Data Model از ابتدا دیده می‌شود، اما Automation سنگین فقط بعد از اثبات بازار و ورود چند مشتری واقعی ساخته خواهد شد.**

---

# 61. سند بعدی

سند بعدی باید:

## Lamatech Core Technical Specification v1.0

باشد و شامل این موارد شود:

- Repository Structure
- Core Folder Structure
- Database Schema
- Instance Identity Schema
- Module Manifest
- Module Lifecycle
- Event Contract
- Health Contract
- Update Metadata
- License Contract
- API Conventions
- Auth
- Permissions
- Migration Rules
- Backup Lifecycle
- Restore Workflow
- Theme Contract
- CMS Block Contract
- Payment Adapter
- SMS Adapter
- AI Provider Contract
- AI Tool Contract
- AI Approval Workflow
- Control Plane Protocol
- Remote Update Protocol
- Deployment Checklist
- Denardi Acceptance Criteria
- QA Checklist
- Versioning Policy
- Release Process

---

**Prepared by Lamatech**
**Restaurant & Cafe Modular Platform**
**Product + Operations + Commercial Architecture — v1.2**

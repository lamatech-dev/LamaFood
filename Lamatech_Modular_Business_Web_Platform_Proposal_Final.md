# Lamatech Modular Business Web Platform
## پروپوزال نهایی معماری محصول و پایلوت Denardi

**Prepared by Lamatech**
**Version 1.0 — September 2026**

---

## 1. چشم‌انداز محصول

هدف Lamatech ساخت یک سایت‌ساز عمومی یا یک سرویس ساده QR Menu نیست.

هدف، ایجاد یک **هسته نرم‌افزاری اختصاصی، ماژولار و قابل استفاده مجدد** است که Lamatech بتواند بر مبنای آن برای هر کسب‌وکار یک وب‌سایت مستقل، حرفه‌ای، Responsive، SEO-ready و قابل توسعه راه‌اندازی کند.

هر مشتری:

- دامنه مستقل دارد.
- Deployment مستقل دارد.
- دیتابیس مستقل دارد.
- فایل‌ها و Media مستقل دارد.
- Theme و هویت بصری خودش را دارد.
- ماژول‌های مورد نیاز خودش را دریافت می‌کند.
- در آینده بدون بازسازی کل سایت، قابلیت‌های جدید دریافت می‌کند.

**Denardi اولین Reference Implementation / Pilot این محصول خواهد بود؛ نه خود محصول نهایی.**

---

## 2. مدل معماری

مدل مطلوب:

**Reusable Single-Tenant Modular Platform**

یعنی یک Codebase مادر متعلق به Lamatech داریم، اما برای هر مشتری نسخه‌ای مستقل Deploy می‌شود.

```text
Lamatech Core
   │
   ├── Denardi Deployment
   ├── Cafe B Deployment
   ├── Restaurant C Deployment
   └── Business D Deployment
```

این ساختار عمداً Multi-Tenant SaaS نیست.

### مزایا

- خرابی یا دیتابیس یک مشتری روی مشتری دیگر اثر نمی‌گذارد.
- امنیت و داده‌های هر بیزینس جدا می‌ماند.
- می‌توان هر Deployment را مستقل Backup، Restore و Upgrade کرد.
- Lamatech مجبور نیست برای هر پروژه همه چیز را از صفر بنویسد.
- محصول قابلیت فروش مکرر و توسعه بلندمدت پیدا می‌کند.

---

## 3. سه لایه اصلی محصول

### Core
قابلیت‌های عمومی و پایدار که تقریباً همه پروژه‌ها نیاز دارند.

### Theme / Business Configuration
مواردی که برای هر مشتری تغییر می‌کنند:

- نام
- لوگو
- رنگ
- فونت
- تصاویر
- Layout
- اطلاعات تماس
- زبان
- Social Links
- SEO
- Feature Flags

### Modules
قابلیت‌هایی که می‌توانند مستقل نصب، فعال، غیرفعال، Upgrade یا Rollback شوند.

---

# 4. Core Platform

Core باید کوچک، پایدار و قابل توسعه باشد.

## 4.1 Business Configuration

مدیریت:

- نام کسب‌وکار
- لوگو
- Favicon
- شعار
- تلفن
- آدرس
- موقعیت جغرافیایی
- شبکه‌های اجتماعی
- ساعت کاری
- زبان‌ها
- واحد پول
- تنظیمات Domain
- تنظیمات Theme

## 4.2 کاربران و سطح دسترسی

Role-Based Access Control از ابتدا در Core باشد.

Roleهای نمونه:

- Lamatech Super Admin
- Business Owner
- Manager
- Content Editor
- Cashier
- Staff

هر ماژول Permissionهای خودش را Register می‌کند.

```text
menu.edit
menu.publish
orders.view
orders.update
payments.view
customers.manage
settings.manage
```

---

# 5. CMS اختصاصی Lamatech

WordPress استفاده نمی‌شود.

CMS داخل خود محصول قرار می‌گیرد، ولی قرار نیست Page Builder پیچیده‌ای مثل Elementor ساخته شود.

مدل پیشنهادی:

**Controlled Block-Based CMS**

Blockهای استاندارد:

- Hero
- About
- Gallery
- Menu Preview
- Products
- Features
- Offers
- Reviews
- FAQ
- Location
- Contact
- Instagram
- CTA
- Footer

Owner می‌تواند:

- Block را روشن/خاموش کند.
- ترتیب Blockها را تغییر دهد.
- محتوا و عکس را عوض کند.
- تنظیمات محدود Layout را تغییر دهد.

اما ساختار Design System حفظ می‌شود.

---

# 6. Design System

یکی از نقاط تمایز Lamatech باید کیفیت بصری محصول باشد.

هر پروژه یک Theme اختصاصی روی Design System مشترک Lamatech خواهد داشت.

Theme شامل:

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
Animations
Mobile Behaviour
Dark / Light Rules
```

Backend هیچ محدودیتی برای ظاهر ایجاد نمی‌کند.

خروجی Frontend می‌تواند:

- Premium
- Animated
- Mobile-first
- App-like
- Responsive
- RTL-ready
- High-end

باشد.

---

# 7. Stack فنی پیشنهادی

## Backend
**Laravel**

## Database
**MySQL / MariaDB**

## Frontend عمومی
Laravel + Modern Frontend Components

برای صفحات عمومی که SEO مهم است، Server-rendered HTML یا SSR-friendly rendering باید حفظ شود.

## Admin UI
**React + Vite**

## PWA
Customer PWA + Admin PWA

Node فقط هنگام Build لازم است، نه لزوماً روی Production Server.

---

# 8. چرا این Stack؟

- روی بسیاری از هاست‌های PHP/MySQL ایران قابل اجراست.
- برای VPS و Cloud هم قابل ارتقاست.
- Laravel برای Auth، Permissions، Migrations، Queue، Scheduler و Module Contracts بالغ است.
- React/Vite برای UI حرفه‌ای و پنل موبایل مناسب است.
- MySQL/MariaDB برای Order، Payment، Loyalty و Inventory مناسب است.
- معماری می‌تواند با رشد پروژه Redis، CDN، Object Storage و Worker اضافه کند.

---

# 9. Database Architecture

هر مشتری دیتابیس خودش را دارد.

```text
denardi_database
restaurant_b_database
cafe_c_database
```

Schema فقط توسط Migration مدیریت می‌شود.

هیچ تغییر Production Database نباید دستی انجام شود.

وقتی Ordering Module نصب می‌شود، جداول مرتبط از طریق Migration ساخته می‌شوند:

```text
orders
order_items
order_status_history
```

---

# 10. Migration System

هر نسخه Core و هر Module باید Migration خودش را داشته باشد.

```text
Menu 1.0
Menu 1.1
Menu 1.2
Ordering 1.0
Payment 1.0
```

فرآیند Upgrade:

```text
Current DB Version
        ↓
Check Required Migrations
        ↓
Create Backup
        ↓
Run Migrations
        ↓
Health Check
```

---

# 11. Backup & Restore

Backup بخشی از Core است، نه قابلیت جانبی.

## Database Backup
مثلاً روزانه.

## Full Backup
Database + Uploads + Configuration.

مثلاً هفتگی.

## Pre-Update Restore Point
قبل از:

- Core Upgrade
- Module Install
- Module Update
- Database Migration

به‌صورت خودکار Restore Point ساخته شود.

از پنل Lamatech باید بتوان:

- Backupها را دید.
- Backup دانلود کرد.
- Restore اجرا کرد.
- آخرین Restore Point را برگرداند.
- Maintenance Mode را فعال کرد.

---

# 12. Module Manager

یکی از مهم‌ترین دارایی‌های آینده محصول.

هر ماژول Manifest خودش را دارد.

```text
Module:
Ordering

Version:
1.3.0

Core Requirement:
>= 1.5

Dependencies:
Menu >= 1.2

Database Migrations:
3

Permissions:
orders.view
orders.manage
orders.cancel
```

## Lifecycle ماژول

```text
Available
↓
Pre-flight Check
↓
Backup
↓
Install
↓
Database Migration
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

همچنین باید تعریف شوند:

```text
Disable
Update
Rollback
Uninstall
```

## Compatibility Check

قبل از Upgrade:

```text
Core Version
PHP Version
Database Version
Module Dependencies
Storage
Required Extensions
```

اگر ناسازگار بود، Update انجام نشود.

---

# 13. ماژول Digital Menu / Catalog

برای Denardi اولین ماژول اصلی است.

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

مثال:

```text
Coffee
  ├ Espresso
  ├ Cappuccino
  ├ Latte

Shake
Smoothie
Matcha
Tea
Dessert
```

---

# 14. QR Engine

QR باید یک Module/Service مستقل باشد.

انواع QR:

- General Menu QR
- Table QR
- Campaign QR
- Product QR
- Landing QR

Analytics:

- تعداد Scan
- زمان Scan
- میز
- Campaign
- Landing Page
- Device
- Language

---

# 15. Online Ordering Module

در Phase بعدی:

- Cart
- Add-ons
- Variants
- Table Order
- Takeaway
- Delivery
- Customer Note
- Order Status
- Kitchen Status

---

# 16. Table System

هر میز QR اختصاصی خواهد داشت.

```text
Table 01
Table 02
Table 03
```

مثلاً:

```text
Order Source = Table 7
```

---

# 17. Iranian Payment Module

پرداخت باید Adapter-Based طراحی شود.

Interface:

```text
PaymentProvider
```

Adapterها:

```text
ZarinpalProvider
IDPayProvider
NextPayProvider
CustomBankProvider
```

در نتیجه Provider می‌تواند بدون تغییر Ordering Module عوض شود.

---

# 18. SMS Provider System

همین معماری برای SMS.

Core فقط Contract را می‌شناسد:

```text
SmsProvider
```

Adapterهای احتمالی:

```text
Kavenegar
Melipayamak
FarazSMS
SMS.ir
CustomProvider
```

کاربردها:

- OTP
- Reservation
- Order Confirmation
- Loyalty
- Campaign
- Admin Alert

---

# 19. Notification Engine

Notification نباید فقط به SMS وابسته باشد.

```text
Notification
 ├ SMS
 ├ Email
 ├ Push
 └ In-App
```

ماژول‌ها به Notification Contract وصل می‌شوند، نه مستقیم به سرویس‌دهنده.

---

# 20. Reservation Module

قابلیت‌های آینده:

- Table Reservation
- Capacity
- Date
- Time
- Confirmation
- Cancellation
- Reminder
- Customer History

---

# 21. Loyalty Club

یکی از مهم‌ترین Upsellهای آینده.

- Customer Account
- Points
- Credit
- Level
- Coupon
- Birthday Reward
- Purchase History
- Referral
- Offers
- Membership QR

---

# 22. CRM

CRM ساده مخصوص کسب‌وکارهای محلی.

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

Segmentها:

```text
VIP
Inactive
New Customer
High Spender
Frequent Visitor
```

---

# 23. Inventory

برای مشتری‌هایی که نیاز دارند:

- Product Stock
- Ingredient Stock
- Low Stock Alert
- Stock Movement
- Consumption
- Waste

برای Denardi V1 الزامی نیست.

---

# 24. Coupon & Campaign Engine

قابل استفاده در:

- Menu
- Ordering
- Loyalty
- QR Campaign

مثال:

```text
20% Coffee Discount
Happy Hour
Birthday Coupon
First Order
QR Campaign
```

---

# 25. Analytics

نسخه Pilot:

- Page Views
- QR Scans
- Popular Categories
- Popular Products
- Device
- Language

نسخه پیشرفته:

- Order Conversion
- Average Basket
- Repeat Customer
- Campaign Performance
- Revenue Analytics

---

# 26. PWA مشتری

سایت عمومی باید PWA-ready باشد.

- Web App Manifest
- Icons
- Splash
- Cache
- Offline Shell
- Installability

---

# 27. Admin PWA

پنل مدیریت باید کاملاً Mobile First باشد.

صاحب کافه از گوشی بتواند:

- قیمت تغییر دهد.
- محصول اضافه کند.
- عکس اضافه کند.
- Sold Out کند.
- سفارش ببیند.
- رزرو ببیند.
- مشتری ببیند.
- گزارش ببیند.
- محتوا را ویرایش کند.

پنل روی Android و iPhone مثل App قابل نصب باشد.

---

# 28. Native App

فعلاً ضروری نیست.

Backend باید API-ready باشد تا بعداً بتوان:

- Android
- iOS
- Flutter
- React Native

را بدون تغییر Core توسعه داد.

---

# 29. SEO Engine

Lamatech فقط Menu نمی‌فروشد.

**Digital Presence** می‌فروشد.

هر سایت از ابتدا:

- Semantic HTML
- Mobile Optimization
- Meta Title
- Meta Description
- Canonical
- OpenGraph
- Sitemap
- robots.txt
- Image Alt
- Clean URLs
- LocalBusiness Schema
- Restaurant/Cafe Schema
- Product/Menu Schema در صورت مناسب بودن
- Location Information

خواهد داشت.

---

# 30. Localization

Core از ابتدا multilingual باشد.

برای Denardi:

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

بدون تغییر معماری قابل اضافه شدن باشند.

---

# 31. Media Library

- Image Upload
- Resize
- Crop
- Thumbnail
- WebP
- AVIF در صورت پشتیبانی
- Alt
- Title
- Caption
- File Size
- Delete
- Reuse

---

# 32. Security

از ابتدا:

- CSRF Protection
- Rate Limiting
- Password Hashing
- Secure Sessions
- Upload Validation
- Permission Checks
- Audit Log
- Login Attempt Protection
- Environment Secrets
- Database Credentials خارج از Public Folder
- Secure Headers
- Validation / Sanitization
- File MIME Validation

---

# 33. Audit Log

برای Product شدن ضروری است.

مثال:

```text
Manager Ali
changed Cappuccino price
230 → 250
2026-09-01 14:52
```

یا:

```text
Lamatech Admin
installed Ordering 1.1.0
```

---

# 34. System Health

پنل Lamatech باید Health Dashboard داشته باشد.

```text
Core Version
PHP Version
Database Version
Storage Usage
Last Backup
Cron Status
Module Versions
Queue Status
Error Logs
SSL
Disk Space
```

---

# 35. Versioning

Core:

```text
1.0.0
1.1.0
1.2.0
```

Moduleها مستقل:

```text
Menu 1.4.0
QR 1.2.0
Ordering 1.0.0
```

---

# 36. Update Manager

در آینده Lamatech بتواند ببیند:

```text
Current Core: 1.4
Latest Core: 1.6

Menu:
1.3 → 1.5
```

Upgrade باید کنترل‌شده، Backup-aware و Migration-aware باشد.

---

# 37. Deployment Profiles

## Profile A — Shared Hosting

مناسب برای:

- Landing
- CMS
- Menu
- QR
- Basic Analytics

## Profile B — VPS

برای:

- Ordering
- Payments
- Queue
- SMS
- Loyalty
- CRM

## Profile C — Advanced

برای مشتری بزرگ:

- Redis
- CDN
- Object Storage
- Advanced Queue
- Monitoring
- Load Scaling

---

# 38. اصل مهم زیرساخت

کد نباید به Netafraz، HostIran، cPanel یا Cloud Provider خاص وابسته باشد.

Hosting فقط Environment اجراست.

محصول باید Portable باشد.

---

# 39. Denardi Pilot

در Denardi کل Roadmap را نمی‌سازیم.

نسخه Denardi فقط اولین **Vertical Slice** محصول است.

## Denardi V1 — Modules ON

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
- User / Role
- Backup
- Audit پایه

## Modules OFF but Compatible

- Online Ordering
- Iranian Payment
- Reservation
- Loyalty
- CRM
- Inventory
- Coupons
- SMS
- Push
- Advanced Analytics

---

# 40. چرا این تصمیم مهم است؟

اگر همین الان همه سیستم‌ها را برای Denardi بسازیم:

- پروژه طولانی می‌شود.
- درآمد اولیه عقب می‌افتد.
- Complexity بالا می‌رود.
- Featureهایی می‌سازیم که هنوز مشتری نخواسته.

در عوض:

**Infrastructure را درست می‌سازیم ولی Featureها را Just-in-Time توسعه می‌دهیم.**

---

# 41. اولین چرخه توسعه

## مرحله اول — Foundation

- Repository
- Laravel
- Database
- Environment
- Core Module Contract
- Business Config
- Users
- Roles
- Theme Engine
- Base API

## مرحله دوم — CMS

- Pages
- Blocks
- Media
- SEO
- Localization

## مرحله سوم — Menu

- Categories
- Products
- Variants
- Add-ons
- Availability

## مرحله چهارم — QR

- QR Generator
- Tracking
- Menu QR

## مرحله پنجم — Admin PWA

- Mobile UI
- Dashboard
- Product Editor
- Menu Management

## مرحله ششم — Reliability

- Backup
- Restore
- Audit
- Module Version
- Health Check

## مرحله هفتم — Denardi

- Theme
- Content
- Products
- Images
- SEO
- QA
- Deployment

---

# 42. مدل فروش آینده

Lamatech می‌تواند محصول را به صورت لایه‌ای بفروشد:

### Base Website
Landing + CMS + SEO

### Digital Menu Module
Catalog + QR + Admin

### Ordering Module
Online/Table Ordering

### Payment Module
Iranian Payment Gateway

### Reservation Module
Table/Time Booking

### Customer Club
Loyalty + Points + Offers

### CRM
Customer Database + Segmentation

یعنی هر مشتری در طول زمان Moduleهای جدید خریداری می‌کند.

این مدل **Customer Lifetime Value** ایجاد می‌کند و Lamatech را از فروش یک‌باره سایت خارج می‌کند.

---

# 43. Asset واقعی Lamatech

دارایی اصلی سایت Denardi نیست.

دارایی اصلی:

```text
Lamatech Core
+
Lamatech Design System
+
Lamatech Module SDK
+
Lamatech Module Library
+
Lamatech Deployment Process
+
Lamatech Update System
```

خواهد بود.

هر مشتری جدید باعث قوی‌تر شدن این Asset می‌شود.

---

# 44. تصمیم نهایی معماری

> **Laravel Modular Monolith + MySQL/MariaDB + Modern React-based Admin + SSR-friendly public website + PWA + Module Contracts + Database Migrations + Automated Backup/Restore + Provider Adapters + Independent Deployment per Customer**

و:

> **Denardi را سریع می‌سازیم، اما هیچ بخشی از آن Throwaway Code نخواهد بود.**

تعادل اصلی پروژه:

**نه Overengineering قبل از فروش، نه کدنویسی یک‌بارمصرف.**

---

# 45. سند بعدی

بعد از این Proposal، سند بعدی باید:

## Lamatech Core Technical Specification v1.0

باشد و دقیقاً شامل این موارد شود:

- Folder Structure
- Database Schema
- Module Contract
- Module Manifest
- API Conventions
- Authentication Flow
- Permissions Model
- Migration Rules
- Backup Lifecycle
- Restore Workflow
- Provider Adapter Interfaces
- PWA Structure
- Theme Contract
- CMS Block Contract
- SEO Contract
- Deployment Checklist
- Denardi Acceptance Criteria
- QA Checklist
- Versioning Policy
- Release Process

---

**Prepared by Lamatech**
**Modular Business Web Platform — Product Architecture Proposal**

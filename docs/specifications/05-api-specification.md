# 05 — API Specification

## اصول

- Base path پنل: `/api/admin/v1`
- Public content عمدتاً server-rendered است؛ API عمومی فقط برای نیاز تعاملی مشخص ایجاد می‌شود.
- JSON با UTF-8، زمان ISO-8601 UTC و ID بیرونی به‌صورت `public_id`.
- نسخه API در URL است و breaking change فقط در نسخه جدید انجام می‌شود.
- CSRF برای session requestها اجباری است.

## Envelope

```json
{
  "data": {},
  "meta": {"requestId": "01..."},
  "errors": []
}
```

خطا:

```json
{
  "data": null,
  "meta": {"requestId": "01..."},
  "errors": [{
    "code": "PRODUCT_SLUG_CONFLICT",
    "message": "عنوان یا نشانی تکراری است.",
    "field": "slug"
  }]
}
```

## Pagination و Filtering

- `page[number]`, `page[size]`؛ اندازه پیش‌فرض 25 و حداکثر 100
- `sort=position` یا `sort=-createdAt`
- filterهای whitelist شده؛ filter دلخواه SQL ممنوع
- search حداکثر 100 نویسه و rate-limited

## Auth

| Method | Path | کاربرد |
|---|---|---|
| POST | `/login` | ورود و rotate session |
| POST | `/logout` | ابطال session جاری |
| POST | `/forgot-password` | ارسال لینک reset |
| POST | `/reset-password` | تغییر password با token |
| GET | `/me` | user، roles، permissions و business context |

Login محدودیت نرخ و lock تدریجی دارد. پاسخ forgot-password وجود یا عدم وجود حساب را افشا نمی‌کند.

Godfather فقط از endpointهای Auth عمومیِ پنل برای ورود خود استفاده می‌کند. هیچ endpoint مدیریت Business اجازه list/search/count/view/update/delete/disable/password/role/permission mutation آن را ندارد و responseهای `/me` نیز marker داخلی آن را افشا نمی‌کنند. bootstrap/rotation آن API ندارد و فقط command Lamatech مبتنی بر env است.

## Business و Theme

| Method | Path | Permission |
|---|---|---|
| GET | `/business` | `settings.view` |
| PATCH | `/business` | `settings.manage` |
| GET | `/theme` | `theme.view` |
| PATCH | `/theme` | `theme.manage` |

فیلدهای Domain، License، Instance ID و Provider secrets از endpoint عمومی settings قابل تغییر نیستند.

## Pages و Blocks

| Method | Path | Permission |
|---|---|---|
| GET/POST | `/pages` | `cms.view` / `cms.edit` |
| GET/PATCH | `/pages/{id}` | `cms.view` / `cms.edit` |
| POST | `/pages/{id}/blocks` | `cms.edit` |
| PATCH/DELETE | `/blocks/{id}` | `cms.edit` |
| POST | `/pages/{id}/preview-token` | `cms.preview` |
| POST | `/pages/{id}/publish` | `cms.publish` |
| POST | `/pages/{id}/restore-revision` | `cms.publish` |

Publish به `expectedRevision` نیاز دارد تا overwrite هم‌زمان با پاسخ 409 متوقف شود.

Payload ساخت/ویرایش Block دو بخش صریح دارد:

```json
{
  "type": "hero",
  "structure": {"mediaId": "...", "variant": "split", "alignment": "start"},
  "translations": {
    "fa": {"content": {"title": "...", "body": "..."}, "state": "ready"},
    "en": {"content": {"title": "...", "body": "..."}, "state": "draft"},
    "ar": {"content": {"title": "...", "body": "..."}, "state": "draft"}
  }
}
```

Server، `structure` و هر locale را جداگانه مقابل schema نوع Block validate و در `blocks`/`block_translations` ذخیره می‌کند. locale key داخل `structure` پذیرفته نمی‌شود.

`GET /business/locales` metadata فعال شامل `code`, `nativeName`, `direction`, `isDefault`, `position` را برمی‌گرداند. API هیچ فهرست hardcoded دو‌زبانه ندارد.

## Menu

| Method | Path | Permission |
|---|---|---|
| GET/POST | `/categories` | `menu.view` / `menu.edit` |
| PATCH/DELETE | `/categories/{id}` | `menu.edit` |
| POST | `/categories/reorder` | `menu.edit` |
| GET/POST | `/products` | `menu.view` / `menu.edit` |
| GET/PATCH/DELETE | `/products/{id}` | `menu.view` / `menu.edit` |
| PATCH | `/products/{id}/publication-state` | `menu.publish` |
| GET/PUT | `/products/{id}/branches/{branchId}/settings` | `menu.view` / `menu.price` یا `menu.availability` |

Product endpoint فقط Catalog fields و `publicationState` را مدیریت می‌کند. `publicationState` یکی از `draft/published/inactive/archived` است.

Branch settings شامل `priceAmount`, `availabilityState`, `expectedVersion` و reason اختیاری است. `availabilityState` فقط `available/sold_out` است. پاسخ تغییر، مقدار قبلی/جدید و audit ID را برمی‌گرداند. API قیمت/availability روی خود Product وجود ندارد.

## Media

- `POST /media/uploads` با multipart، محدودیت MIME/size و checksum
- `GET /media`
- `PATCH /media/{id}` برای alt/title/caption
- `DELETE /media/{id}` فقط بدون usage؛ در غیر این صورت 409

Upload ابتدا `processing` است و فقط پس از validation/derivative generation به `ready` تبدیل می‌شود.

## QR و Analytics

| Method | Path | Permission |
|---|---|---|
| GET/POST | `/qr-codes` | `qr.view` / `qr.manage` |
| PATCH | `/qr-codes/{id}` | `qr.manage` |
| GET | `/qr-codes/{id}/download?format=svg|png|pdf` | `qr.view` |
| GET | `/analytics/summary` | `analytics.view` |
| GET | `/analytics/products` | `analytics.view` |
| GET | `/analytics/qr` | `analytics.view` |

بازه Analytics حداکثر 366 روز در V1 است.

در V1 `type` ساخت QR فقط `general_menu` و `table` است. درخواست `campaign` با error code پایدار `QR_TYPE_NOT_ENABLED` رد می‌شود؛ Campaign QR تا نیازمندی امضاشده Deferred است.

## عملیات Lamatech

- `GET /system/health`
- `GET /system/version`
- `GET /system/instance-metadata`
- `GET /backups`
- `POST /backups`
- `POST /backups/{id}/verify`
- `POST /backups/{id}/restore`
- `GET /audit-logs`

Restore نیازمند Lamatech Super Admin، re-authentication، confirmation phrase و maintenance mode است.

Instance metadata شامل ID و versionهای محلی است. هیچ endpoint تماس با license server، activate/deactivate license یا remote enforcement در V1 وجود ندارد.

## Idempotency و Rate Limit

- عملیات Backup/Restore/Publish و future Payment از `Idempotency-Key` پشتیبانی می‌کنند.
- API Admin بر اساس user و IP prefix rate limit دارد.
- endpoint ثبت Analytics در لبه یا application با dedupe و bot filter محافظت می‌شود.

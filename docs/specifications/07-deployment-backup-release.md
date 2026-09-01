# 07 — Deployment, Backup & Release Runbook

## محیط‌ها

- Local: داده ساختگی، mail fake، بدون secret Production
- Staging: مشابه Production، داده sanitize‌شده، دسترسی محدود
- Production: Denardi instance با DB، storage و secret مستقل

## Production Reference Profile

- Linux VPS پشتیبانی‌شده
- Nginx/Apache + PHP-FPM 8.3+
- MySQL 8/MariaDB معادل
- cron هر دقیقه برای Laravel scheduler
- database queue worker با process supervisor
- TLS خودکار، outbound HTTPS و external backup storage

Shared Hosting فقط اگر scheduler، queue fallback، storage permission، outbound HTTPS و restore workflow تست شوند مجاز است.

## CI Pipeline

1. composer validation/install با lockfile
2. PHP lint و formatter check
3. static analysis
4. unit/feature tests
5. npm clean install با lockfile
6. frontend lint/typecheck/test/build
7. dependency/security scan
8. build immutable release artifact
9. manifest و checksum

## Release Flow

```text
Approve Release
→ Verify compatibility
→ Backup + checksum
→ Enable maintenance if migration requires
→ Deploy new artifact to new release directory
→ Run forward migrations
→ Warm config/routes/views/cache
→ Switch current symlink
→ Restart workers
→ Smoke + health checks
→ Disable maintenance
→ Observe
```

Deploy با artifact انجام می‌شود، نه `git pull` روی Production.

## Migration Policy

- migration قبل از code switch فقط اگر backward-compatible باشد.
- عملیات طولانی داده‌ای در job/chunk انجام می‌شود.
- rename/drop با expand → migrate → contract و حداقل یک Release فاصله.
- هر migration روی snapshot مشابه Production در Staging تست می‌شود.

## Rollback

- code-only: بازگشت symlink و restart worker
- schema backward-compatible: code rollback بدون DB rollback
- data/schema incompatible: maintenance + restore point
- تصمیم rollback حداکثر پس از مشاهده health/smoke بحرانی گرفته می‌شود.

## Backup Policy پیشنهادی

| نوع | تناوب | نگهداری پایه |
|---|---|---|
| Database | روزانه | 14 نسخه |
| Full DB + uploads + non-secret config manifest | هفتگی | 8 نسخه |
| Pre-release restore point | هر Release | 4 Release یا 30 روز |
| قبل از Module/Migration | هر عملیات | تا تأیید پایداری |

حداقل یک نسخه encrypted خارج از سرور اصلی نگهداری می‌شود.

## Secret Exclusion و Recovery

- `.env`, `APP_KEY`, TLS private keys، SSH keys و Provider plaintext secrets در هیچ Backup archive عادی قرار نمی‌گیرند.
- DB dump شامل secret fieldها فقط به‌صورت ciphertext تولیدشده توسط Application است.
- Full Backup یک manifest غیرحساس از secretهای مورد نیاز و version/reference آن‌ها دارد.
- recovery material در password manager/escrow رمزنگاری‌شده، جدا از server و backup destination نگهداری می‌شود.
- ترتیب Restore: ساخت محیط پاک → provision کردن `APP_KEY`/credentials از escrow مجاز → restore DB/files → validation decrypt/provider → rotate در صورت احتمال compromise.
- اگر recovery key در دسترس نباشد، DB و Media قابل Restore هستند ولی Provider secretها باید دوباره صادر/تنظیم شوند؛ این وضعیت در Runbook و health result صریح ثبت می‌شود.

## RPO و RTO

- V1 هدف RPO: حداکثر 24 ساعت برای CMS/Menu
- هدف RTO: حداکثر 4 ساعت در ساعات پشتیبانی
- برای Ordering آینده RPO/RTO باید به‌طور جدا و سخت‌گیرانه‌تر تعریف شود.

## Verify و Restore Drill

- checksum هر Backup پس از ساخت بررسی می‌شود.
- Full restore ماهانه روی محیط ایزوله انجام می‌شود.
- تست شامل provision جداگانه secrets، login، count رکوردهای کلیدی، media sample، public menu، decrypt check کنترل‌شده و health است.
- Backup بدون restore drill موفق «قابل اعتماد» محسوب نمی‌شود.

## Monitoring و Alert

- HTTP uptime و TLS expiry
- application error rate و p95 latency
- disk usage، DB connectivity، queue backlog و failed jobs
- scheduler heartbeat
- backup freshness و restore verification
- alert critical به Lamatech؛ داده حساس در alert قرار نمی‌گیرد.

## DNS و Go-live

- مالک Domain و دسترسی DNS ثبت شود.
- TTL پیش از انتقال کاهش یابد.
- SSL، redirect، canonical، sitemap، robots و analytics قبل از اعلام عمومی بررسی شوند.
- rollback DNS فقط در Runbook مهاجرت Domain و با TTL شناخته‌شده انجام شود.

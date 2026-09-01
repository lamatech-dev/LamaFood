# LamaFood

LamaFood is Lamatech's modular, single-tenant restaurant platform. The repository contains the approved Core Foundation plus the functional Denardi V1 Admin/CMS/Menu/Media slice for local development and review.

## Foundation baseline

- Laravel 13.29.0 on PHP 8.4.25
- MySQL 8.4.11 as the authoritative database contract
- Node.js 24.20.0 and npm 11.9.0
- Business/Branch, Sanctum authentication, Business-scoped RBAC and append-only audit
- Instance metadata and bundled module contracts without central license enforcement or runtime plugin installer
- Canonical integer IRR money handling
- Data-driven Denardi locales: Persian (`fa`, RTL, default), English (`en`, LTR) and Arabic (`ar`, RTL)
- Env-bootstrapped Lamatech Godfather account, invisible to Business user-management surfaces
- Structured Admin workspaces for products, categories, CMS pages/blocks, Media and mandatory V1 QR types
- Three-language CMS publishing readiness, authenticated draft preview and immutable published snapshots
- Business-level products with branch-specific IRR price and availability settings
- Original image preservation with optimized and thumbnail WebP derivatives

## Documents

- [Denardi V1 delivery tracker](docs/DENARDI_V1_TRACKER.md)
- [Denardi V1 visual dashboard](docs/DENARDI_V1_TRACKER.html)
- [Specification index](docs/specifications/README.md)
- [Foundation development](docs/FOUNDATION_DEVELOPMENT.md)
- [Foundation implementation report](docs/FOUNDATION_IMPLEMENTATION_REPORT.md)

## Local quality gates

```bash
php artisan test
composer analyse
vendor/bin/pint --format agent
npm run build
composer audit
npm audit --audit-level=high
```

See `docs/FOUNDATION_DEVELOPMENT.md` for MySQL setup and the safe Godfather bootstrap/rotation procedure.

Campaign QR, staging/production deployment, real Denardi content population, password reset and visual redesign remain outside this completion slice.

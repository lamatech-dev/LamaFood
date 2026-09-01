# LamaFood

LamaFood is Lamatech's modular, single-tenant restaurant platform. The repository currently contains the approved Core Foundation only; Denardi feature implementation has not started.

## Foundation baseline

- Laravel 13.29.0 on PHP 8.4.25
- MySQL 8.4.11 as the authoritative database contract
- Node.js 24.20.0 and npm 11.9.0
- Business/Branch, Sanctum authentication, Business-scoped RBAC and append-only audit
- Instance metadata and bundled module contracts without central license enforcement or runtime plugin installer
- Canonical integer IRR money handling
- Data-driven Denardi locales: Persian (`fa`, RTL, default), English (`en`, LTR) and Arabic (`ar`, RTL)
- Env-bootstrapped Lamatech Godfather account, invisible to Business user-management surfaces

## Documents

- [Specification index](docs/specifications/README.md)
- [Foundation development](docs/FOUNDATION_DEVELOPMENT.md)
- [Foundation implementation report](docs/FOUNDATION_IMPLEMENTATION_REPORT.md)

## Local quality gates

```bash
php artisan test
composer analyse
vendor/bin/pint --test
npm run build
composer audit
npm audit --audit-level=high
```

See `docs/FOUNDATION_DEVELOPMENT.md` for MySQL setup and the safe Godfather bootstrap/rotation procedure.

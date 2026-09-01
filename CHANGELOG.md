# Changelog

## Unreleased — Denardi V1

### Admin/CMS/Menu/Media completion

- Completed Page and Block edit, safe delete/archive, reorder, enable/disable, authenticated preview and three-language publish workflow.
- Completed Product and Category edit, reorder and safe delete/archive while keeping publication independent from branch price/availability.
- Added branch selector for Admin price/availability management and primary Media assignment for Products.
- Added original-preserving Media upload with optimized/thumbnail WebP derivatives, localized metadata, usage references and in-use deletion protection.
- Added Media selection for CMS blocks and immutable Media metadata in published snapshots.
- Reorganized the Admin shell into Overview, Products, Categories, Content, Media, QR/Analytics and Localization sections.
- Expanded the idempotent local-only Denardi demo dataset to four categories and nine three-language products, including draft and sold-out examples.
- Added focused CMS, Menu and Media management tests and browser smoke checks for Persian and Arabic public menus.
- Added CI failure summaries/annotations so repository-controlled test failures remain visible and auditable without exposing stored GitHub credentials.
- Fixed CMS preview test isolation so clean CI checkouts do not depend on a locally generated Vite manifest.
- Added a single Denardi V1 delivery tracker for subsystem progress, client requirement traceability, release gates and external blockers.
- Expanded the tracker into a persistent 159-item canonical checklist plus a self-contained visual HTML delivery dashboard.

### Verification

- Local MySQL: 65 PHPUnit tests and 315 assertions pass.
- PHPStan/Larastan, Pint, frontend tests, Vite production build, Composer validation and dependency audits pass locally.
- Fix commit `047830a` passed GitHub Actions run `33567423994` on disposable MySQL 8.4.11, including migrations from zero, PHP tests, PHPStan, Pint, frontend build/tests and dependency audits.

## Foundation checkpoint

### Added

- Laravel 13 Foundation bootstrap with pinned PHP, Composer, Node, npm and MySQL policies
- Business, Branch, BusinessLocale, User/Auth, RBAC, Audit and Instance metadata foundations
- Configuration-driven Persian, English and Arabic locale metadata with RTL/LTR direction
- Canonical integer IRR Money value object
- Event envelope and bundled Module contract/registry/state
- Liveness, authenticated health and instance metadata endpoints
- Env-bootstrapped, Business-invisible Godfather Lamatech account with Gate bypass and Audit integration
- PHPUnit, Larastan, Pint, Vite and dependency-audit quality gates

### Decisions and refinements

- Godfather is represented by a protected internal User marker and standard Laravel Gate bypass, not a customer-visible role, hidden route or master password.
- Godfather credentials are bootstrap/rotation inputs from local env; only safe placeholders exist in tracked files.
- Denardi's required locales are `fa`, `en` and `ar`; direction comes exclusively from locale metadata and public fallback is disabled.
- Module delivery is bundled/configured only. Runtime package installation and marketplace behavior remain deferred.
- Instance/license metadata is informational only; no remote enforcement was added.
- Local MySQL 8.4 is now available for application migrations, provisioning and behavioral tests. GitHub Actions continues to validate from-zero migrations against its disposable MySQL 8.4.11 service.

# Changelog

## Unreleased — Foundation

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
- Local MySQL 8.4.11 validation is pending because the available macOS binary crashes and the host has insufficient free disk for a safe isolated install. CI remains pinned to the official MySQL 8.4.11 container and has no SQLite fallback.

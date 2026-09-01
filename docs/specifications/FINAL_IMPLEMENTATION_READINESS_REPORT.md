# FINAL IMPLEMENTATION READINESS REPORT

**Status:** Architecture-corrected; Foundation implementation started on 2026-09-01.
**Scope of this report:** This was the pre-implementation readiness baseline. Current delivery evidence is maintained in `docs/FOUNDATION_IMPLEMENTATION_REPORT.md`.

## Blocking issues remaining

### Resolved before repository/bootstrap implementation

1. Runtime baseline is pinned to Laravel `13.29.0`, PHP `8.4.25`, Node `24.20.0`, npm `11.9.0`, Composer `2.10.3` and MySQL `8.4.11`.
2. Canonical money storage is integer `IRR`; Denardi's display convention is thousand toman at the presentation boundary.

### Before Denardi feature completion/Production

1. Obtain signed Scope/UAT confirmation that V1 contains General Menu QR and Table QR, while Campaign QR is deferred.
2. Receive Denardi's final brand assets, three-language content, menu/prices, table list, map/Instagram details and content approver.
3. Confirm Production hosting ownership/access and the external encrypted secret escrow/password-manager owner required by the recovery runbook.

These items do not block Core Foundation work, but they block complete Denardi delivery and Go-live.

## Non-blocking TBDs

- Setup/module/license pricing and tax treatment
- SLA response/resolution targets and official support channel
- Backup/Audit retention by commercial plan
- Storage and future AI/SMS quotas
- Theme source-code ownership wording
- License grace period and future policy
- Future multi-branch management/reporting UX
- Variant/Add-on branch-pricing UI unless confirmed by Denardi's final menu
- Campaign QR design until a signed requirement exists

## V1 implementation order

1. Pin runtime versions; create repository, CI quality gates and environment templates.
2. Build Core foundation: configuration, Business/Branch, Auth/RBAC, audit base and local instance/version metadata.
   Include the env-bootstrapped, business-invisible Godfather Lamatech account through the standard Gate/Audit foundation.
3. Build locale metadata foundation for `fa/rtl` (default), `en/ltr`, `ar/rtl`, then Media/CMS with explicit `blocks.structure_json` + `block_translations.content_json` schemas and locale readiness validation.
4. Build Denardi public Theme/Landing, FA/EN/AR routing, accessibility and SEO.
5. Build Menu catalog: Business-level Product/Category/translation plus Product↔Branch price/availability settings and normalized lifecycle.
6. Build Admin flows for CMS, catalog, Branch price and `available/sold_out` updates.
7. Build General Menu QR and Table QR, print outputs and minimal first-party scan/menu analytics.
8. Add PWA installability, cache invalidation and online-first behavior.
9. Add backup/restore, secret-exclusion/recovery, health checks, monitoring and release runbook automation scoped to one Instance.
10. Execute security, performance, accessibility, restore drill, UAT and Production deployment.

## Estimated complexity by subsystem

| Subsystem | Complexity | Note |
|---|---|---|
| Repository/CI/runtime foundation | Medium | Version pinning, build artifact and quality gates |
| Business/Branch configuration | Low | One active Branch, future-safe model |
| Auth/RBAC/Audit | Medium | Permission boundaries and sensitive-action audit |
| CMS + localized Block schemas | High | Revisions, per-locale readiness, schema validation and preview/publish |
| Media pipeline | Medium | Secure upload, derivatives, usage protection |
| Public Theme/FA-EN-AR/SEO | Medium | metadata-driven RTL/LTR, performance and structured data |
| Product catalog + Branch settings | High | lifecycle separation, optimistic versioning, cache and future branch correctness |
| Admin Menu workflows | Medium | mobile-first editing and conflict/error states |
| General/Table QR | Medium | stable IDs, print assets, attribution and deduplication |
| Basic analytics | Medium | privacy-preserving events, bot filter and aggregation |
| PWA | Low | installable online-first shell; no offline editing |
| Backup/Restore/Secret recovery | High | external encrypted storage, verification and restore drills |
| Health/monitoring | Medium | single-instance checks only; no Control Plane |
| Local instance/license metadata | Low | informational only; no server/enforcement |
| Bundled Module registration | Medium | contracts/state only; no runtime installer |
| AI foundation contracts | Low | documentation/schema/flags only; no provider or assistant |

## Implementation status amendment

The earlier documentation-only confirmation is superseded by the explicitly authorized Foundation implementation. Foundation code and migrations now exist; Denardi feature implementation (CMS, Menu, QR, public theme and Admin UI) has not started.

Denardi V1 localization architecture supports `fa/en/ar`; Persian remains default, and direction is metadata-driven.

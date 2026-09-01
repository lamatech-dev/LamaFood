# Denardi V1 Delivery Tracker

**Last updated:** 2026-09-02

**Working branch:** `develop/denardi-v1`

**Latest checkpoint:** `f493edf`

**Latest green CI:** [GitHub Actions run 33567600478](https://github.com/lamatech-dev/LamaFood/actions/runs/33567600478)

**Current estimate:** Implementation **82%** · Go-live readiness **55%**

This is the single operational tracker for Denardi V1. Every authorized implementation stage must update its rows, evidence, remaining work and percentage in the same checkpoint commit. Secrets, passwords and production credentials must never be recorded here.

## Status legend

- ✅ Complete and verified
- 🟡 Implemented but final QA/content/approval remains
- ⬜ Not started
- 🔒 Blocked by external input or approval
- ⏸ Explicitly deferred/out of V1

## Implementation tracker

| Area | Status | Progress | Completed | Remaining / exit condition | Evidence |
|---|---:|---:|---|---|---|
| Repository, runtime and CI | ✅ | 100% | Version pins, lockfiles, MySQL 8.4 CI, tests, static analysis, build and audits | Maintain green CI on later checkpoints | `foundation-v1-checkpoint`, run `33567600478` |
| Core Business/Branch architecture | ✅ | 100% | Single-instance Business/Branch model and Business-scoped data | No V1 work remaining | Foundation tests |
| Auth, RBAC, Godfather and Audit | ✅ | 95% | Login/logout, RBAC, invisible Godfather, sensitive-action audit | Final security/UAT review; Password Reset is tracked separately | Auth/RBAC/Audit tests |
| FA/EN/AR localization | ✅ | 95% | Data-driven locale/direction, independent CMS/Menu translations, no silent public fallback | Final real copy and visual RTL/LTR QA | Locale/CMS/Menu tests |
| CMS Pages and Blocks | ✅ | 95% | CRUD, reorder, enable/disable, readiness, preview, immutable publish snapshot and audit | Final Denardi content population and editor UAT | `63cbb4f`, CMS management tests |
| Media pipeline | ✅ | 95% | MIME validation, original preservation, WebP/thumbnail, metadata, references and protected deletion | Populate licensed final assets and visually inspect crops | Media tests |
| Product/Category catalog | ✅ | 95% | Business-level catalog, CRUD/archive, reorder, lifecycle, translations and images | Import/approve final menu; decide whether optional Variant/Add-on UI is actually required | Menu tests |
| Branch price/availability | ✅ | 95% | Independent IRR price and available/sold-out settings with optimistic versioning | Owner mobile UAT using final prices | ProductBranch tests |
| Public Denardi website/menu | 🟡 | 80% | Responsive Home/Menu/About/Contact/Privacy shell, search, category navigation and product cards | Final brand assets/copy/contact/map/hours; final visual refinement | Browser smoke tests |
| SEO | 🟡 | 85% | Canonical, hreflang `fa/en/ar`, x-default, sitemap, robots and localized metadata foundations | Validate final metadata, structured data and Production URLs | SEO tests |
| PWA | 🟡 | 75% | Manifest, service worker and offline shell foundations | Installability/device QA and cache/update behavior on Staging | PWA tests |
| Admin UX | 🟡 | 85% | Structured Overview/Menu/Content/Media/QR/Localization workflows | Final mobile/browser UAT, error-state polish and approved visual refinement | Admin shell + API tests |
| Password Reset | ⬜ | 0% | — | Separate authorized implementation; email/provider and recovery policy required | V1 Scope requirement |
| General/Table QR | ✅ | 95% | Stable QR, table attribution, enable/disable and SVG/PNG/PDF artwork | Receive final table list and perform physical print/scan QA | QR tests, `2b603e8` |
| Basic analytics | 🟡 | 80% | Privacy-preserving scan/menu events and today/7/30-day summary | Verify real traffic, retention decision and dashboard UAT | Analytics tests |
| Backup application lifecycle | 🟡 | 75% | Secret exclusion, DB/full/pre-release types, manifests, verification state and audit | Configure external encrypted destination and perform Staging restore drill | Backup tests, `cd48495` |
| Health and monitoring | 🟡 | 60% | Application/DB/instance health foundations | External uptime/TLS/error/disk/queue/scheduler/backup alerts | Health tests + deployment spec |
| Security release review | 🟡 | 55% | RBAC, isolation, secret checks and dependency audits automated | Focused XSS/IDOR/upload/session/security review in release environment | QA strategy |
| Performance/accessibility/visual QA | ⬜ | 20% | Basic responsive/browser smoke testing only | Lighthouse targets, axe/keyboard/screen-reader, query/p95 and breakpoint matrix | Release gates |
| Staging | ⬜ | 0% | — | Provision Production-like Staging, sanitized data, workers, TLS and restricted access | Deployment runbook |
| Denardi UAT/training | 🔒 | 0% | Technical UAT checklist exists | Real content, Owner/Editor walkthrough, written sign-off and short guide | Denardi approval required |
| Production/DNS/go-live | 🔒 | 0% | Release/rollback contracts documented | Hosting/DNS access, SSL, external backup, deploy, smoke/observe and sign-off | External access required |

## Client requirement traceability

| ID | Requirement | Status | What remains |
|---|---|---:|---|
| DEN-01 | Domain, SSL, hosting and DNS | 🔒 | Hosting ownership/access, selected domain, DNS and Production deployment |
| DEN-02 | Dedicated Landing Page | 🟡 | Final approved brand copy/assets/contact CTA |
| DEN-03 | Responsive digital menu | ✅ | Final device/UAT confirmation |
| DEN-04 | Public FA/EN/AR | 🟡 | Architecture complete; final Arabic/English/Persian content and sign-off pending |
| DEN-05 | Menu management panel | ✅ | Owner UAT with final content/prices |
| DEN-06 | Trackable Table QR | 🟡 | Final table list and physical scan test |
| DEN-07 | Map and Instagram links | 🔒 | Final URLs/contact details from Denardi |
| DEN-08 | Menu search | ✅ | Final dataset/browser QA |
| DEN-09 | Horizontal category navigation | ✅ | Final mobile visual QA |
| DEN-10 | Product cards | 🟡 | Final licensed product images/content |
| DEN-11 | Denardi visual identity | 🟡 | Final visual refinement after brand assets/approval |
| DEN-12 | Printable QR design | 🟡 | Physical stand dimensions, print proof and scan QA |

## Release gates

- [x] Full PHP test suite passes locally on MySQL.
- [x] GitHub Actions passes against disposable MySQL 8.4.11.
- [x] PHPStan/Larastan and Pint pass.
- [x] Frontend tests and production build pass.
- [x] Composer/npm dependency audits report no known High/Critical issue.
- [x] `.env` and local credentials remain ignored/untracked.
- [x] Campaign QR, runtime plugin installer and central license enforcement remain absent/deferred.
- [ ] Password Reset flow implemented and tested.
- [ ] Final Denardi content/assets imported in `fa/en/ar`.
- [ ] All P0 browser flows pass on Chrome Android, Safari iOS and desktop targets.
- [ ] RTL/LTR visual matrix passes at 320, 375, 768, 1024 and 1440px.
- [ ] Lighthouse: Performance ≥85, Accessibility ≥90, Best Practices ≥90, SEO ≥95.
- [ ] Security review has zero unresolved Critical/High findings.
- [ ] Staging environment is deployed and signed off.
- [ ] External encrypted backup destination is active.
- [ ] Full restore drill succeeds on Staging with secrets provisioned separately.
- [ ] All General/Table QR print proofs scan correctly.
- [ ] Denardi provides written UAT/content approval.
- [ ] Production hosting/DNS/SSL/monitoring/rollback checks pass.
- [ ] Owner receives handover/training and a short Admin guide.

## Required external inputs

- [ ] Signed confirmation: General Menu QR + Table QR are V1; Campaign QR remains deferred.
- [ ] Final logo, fonts, brand assets and usage approval.
- [ ] Final Persian, English and Arabic Home/About/Contact copy.
- [ ] Final menu, prices, images, ingredients and allergen notices.
- [ ] Final table count/names and printable stand dimensions.
- [ ] Address, map pin, telephone, Instagram and business hours.
- [ ] Domain owner, DNS access and Production hosting access.
- [ ] Denardi content approver and written UAT sign-off owner.
- [ ] External encrypted secret escrow/password-manager owner.
- [ ] Commercial TBDs: SLA, retention, quotas, pricing/tax and theme ownership language.

## Explicitly deferred — not counted as incomplete V1 work

- ⏸ Campaign QR without a signed Denardi change request
- ⏸ Ordering, payment, kitchen, reservations, CRM, loyalty and inventory
- ⏸ Runtime plugin marketplace/installer and remote update automation
- ⏸ Central Control Plane and central license server/enforcement
- ⏸ OTP/TOTP/SSO/IAM expansion
- ⏸ Operational AI Assistant
- ⏸ Multi-branch management/reporting UX beyond the current branch-aware contracts

## Exact next checkpoint

After approval, execute **Denardi V1 Release Readiness — Password Reset + final browser/accessibility/security QA preparation**. Do not deploy Staging until its environment inputs and credentials are separately authorized.

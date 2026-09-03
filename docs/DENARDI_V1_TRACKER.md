# Denardi V1 Project Tracker

| Project | Branch | V1 Completion | Go-live Readiness | Last updated | Last reviewed commit | CI status | Current blockers |
|---|---|---:|---:|---|---|---|---:|
| Denardi V1 | `develop/denardi-v1` | **95%** | **66%** | 2026-09-03 | `2f59efb` | ✅ [Run 33732532543](https://github.com/lamatech-dev/LamaFood/actions/runs/33732532543) | **3 blocker groups** |

This Markdown file is the canonical project-status document. Percentages are evidence-based planning estimates, not mathematical precision. The tracker contains **167 control items**: 123 Done, 10 Partial, 6 Not Started, 16 Blocked line items and 12 Out of V1. The blocked line items roll up into three actual external blocker groups.

## Maintenance rule

After every major implementation phase or release checkpoint, `DENARDI_V1_TRACKER.md` and `DENARDI_V1_TRACKER.html` must be updated together to reflect the actual repository state. The same checkpoint must update `docs/IMPLEMENTATION_LOG.md`, be committed on `develop/denardi-v1`, pushed to GitHub and pass CI before it is reported complete. Never record secrets, passwords or production credentials.

## Status legend

- ✅ **DONE** — implemented and backed by code/test/CI evidence
- 🟡 **PARTIAL** — useful implementation exists, but required work or final verification remains
- ⏳ **NOT STARTED** — required V1 work has not started
- 🔴 **BLOCKED** — cannot close without external input/access/approval
- ⚪ **OUT OF V1** — explicitly deferred and not counted as incomplete V1 work

## Current State

**Shared mobile navigation update:** The premium floating dock now appears on every public page in FA/EN/AR, with localized routes and page-aware active states. Verified across five pages × three locales × three mobile widths, with no overflow and reserved footer/safe-area space. Admin/desktop are unchanged; see the implementation log for tests. Estimates remain unchanged.

**English review amendment:** English mobile cards use text-left/image-right; the approved FA/AR composition remains unchanged. Verified locally at 320/390/430px with square media, no page overflow, 10 frontend tests and production build passing. Estimates are unchanged.

**2026-09-03 reference-led menu update:** The supplied concept is implemented with large 1:1 media, horizontal mobile composition, compact sticky controls, a mobile navigation dock and responsive 2/3/4-column grids. FA keeps `تومان`; EN/AR use `T`. Final visual comparison is in root `design-qa.md`. FA/EN/AR browser checks at eight widths (320–1440px), 109 PHPUnit tests / 735 assertions, 10 frontend tests, PHPStan, Pint, build and dependency checks passed. Checkpoint `2f59efb` is pushed and GitHub CI [33732532543](https://github.com/lamatech-dev/LamaFood/actions/runs/33732532543) passed, including clean migrations on disposable MySQL 8.4. Estimates remain **95% / 66%** and external dependencies remain open. Previously authorized demo/page/Admin presentation work is preserved; demo content is not approved production content.

Foundation, three-language localization, CMS, Media, Product/Category management, branch pricing/availability, General/Table QR, public menu, required analytics emission, secure Password Reset, business User Management, health checks, structured data, SEO/PWA foundations, secret-safe backup creation, guarded Safe Restore and the final responsive UI/UX pass are implemented. The local quality gate passes. Remaining engineering is Production-like validation; Go-live additionally depends on real Denardi content, Staging, external encrypted backups, restore drill, external monitoring, UAT and Production access.

## Completed

- Core runtime, MySQL migrations, Business/Branch boundaries, RBAC, Godfather and audit
- Data-driven `fa/en/ar` localization with metadata-driven RTL/LTR and explicit fallback policy
- CMS Page/Block CRUD, readiness, draft preview, publish snapshots and audit
- Media upload, original preservation, WebP/thumbnail derivatives and usage protection
- Business-level Product catalog with Branch-specific price/availability and normalized lifecycle
- General/Table QR with stable routes, scan attribution, deduplication and SVG/PNG/PDF output
- Structured Admin workspaces for Menu, CMS, Media, QR, Analytics and Localization
- Canonical/hreflang/sitemap/robots/OpenGraph and online-first PWA/offline-shell foundations
- Secret-excluding DB/Full backup creation, checksum verification, schedule and audit
- Guarded CLI Restore with dry-run preflight, instance/core checks, verified safety backup, maintenance/confirmation/locking guards, streaming archive validation and audit
- Secure normal-user Password Reset with expiry, throttling, Godfather isolation and audit
- Privacy-preserving Menu/Category/Product/QR analytics with branch attribution and deduplication
- Application/database/storage/queue/scheduler/backup-freshness health visibility
- CMS/config-driven LocalBusiness JSON-LD plus production-size PWA icons and installability prerequisites
- Stateful Sanctum/CSRF Admin authentication, baseline security headers, server-protected Admin shell and completed local XSS/IDOR/permission/session/upload/secret matrix
- Analytics device/table breakdown in the Admin summary
- Business-scoped User Management for listing, invite/create, profile/role/status updates, safe deactivation, password reset and audit, with Godfather/Lamatech isolation
- 109 PHPUnit tests / 735 assertions, PHPStan, Pint, 10 frontend tests/build and Composer/npm dependency audits

## Remaining for V1

- Production-like Lighthouse, axe/screen-reader, iOS Safari and Android device QA; local Lighthouse baseline is recorded separately
- Successful full Restore Drill on isolated Staging with recorded recovery evidence
- PWA installation/device validation in a Production-like environment
- Final real Denardi copy/assets/menu data followed by three-language content approval

## Remaining for Go-live

- Signed Scope/UAT confirmation and named Denardi approver
- Production-like Staging with TLS, worker/scheduler and sanitized/final data
- External encrypted offsite backup destination and successful Staging restore drill
- External uptime/TLS/error/disk/queue/scheduler/backup monitoring
- Final domain/DNS/SSL/Production secrets and hosting access
- Full P0 browser/security/performance/accessibility matrix and written UAT sign-off
- QR physical print/scan proof, owner training, handover guide and rollback rehearsal

## Current Blockers

1. **Denardi input/approval:** final brand assets, three-language copy, menu/prices/images/allergens, contact/map/social data, table list and written approver.
2. **Production infrastructure/access:** hosting, domain/DNS, SSL, Production secret owner, external encrypted backup destination and monitoring channels.
3. **Commercial/scope confirmation:** signed General/Table QR scope with Campaign QR deferred, plus SLA/retention/theme-ownership decisions required before Production.

These block complete delivery/go-live, not continued local release-readiness engineering.

## Recommended Execution Order

1. Receive and validate final `fa/en/ar` Denardi content, menu, images, prices, contact data and table list.
2. Import the approved content through the completed Admin workflows and run content-readiness checks.
3. Provision Staging only after explicit environment/credential authorization.
4. Configure offsite encrypted backup/monitoring and complete restore/rollback drills.
5. Validate Production-like Lighthouse, axe/screen-reader, PWA installation, iOS Safari and Android Chrome in Staging.
6. Run Denardi UAT, physical QR proof, training and written sign-off.
7. Perform Production deploy, DNS/SSL switch, smoke checks and observation window.

## Completion Metrics

| Metric | Estimate | Basis |
|---|---:|---|
| Foundation | 100% | Core, migrations, RBAC, metadata, module/event contracts and CI are green |
| Functional V1 | 98% | Required local application flows, including business User Management and guarded Restore, are implemented |
| UI/UX readiness | 91% | Public/Menu/Admin/User Access responsive refinement and browser QA pass; final brand/content/device UAT remains |
| QA readiness | 90% | Release security/user-access matrices, automated gates, local Lighthouse and responsive FA/EN/AR/Admin browser checks pass; Production-like/device/axe/screen-reader validation remains |
| Production readiness | 35% | Backup/Restore implementation and runbooks exist; Staging/offsite/monitoring/Production are not configured |
| Overall V1 completion | **95%** | Weighted estimate after closing Release QA and the locally verifiable business User Management workflow |
| Go-live readiness | **66%** | Local Release QA, User Management and application/Restore foundations pass; content, operational drills, device QA and approval remain |

## Feature Tracker

Columns: **V1** means required for Denardi V1. Priority uses P0 (release-blocking), P1 (required), P2 (polish/future hardening).

### Foundation / Core

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Core | Laravel / PHP runtime | ✅ DONE | Yes | Laravel 13.29 / PHP 8.4 policy and lockfiles | Maintain pins | — | P0 | Complete | CI bootstrap | `foundation-v1-checkpoint` |
| Core | MySQL / migrations | ✅ DONE | Yes | MySQL 8.4 contract; from-zero CI migrations | Maintain migration safety | — | P0 | Complete | Full CI | `foundation-v1-checkpoint` |
| Core | Business context | ✅ DONE | Yes | Single-instance Business scope and resolver | — | — | P0 | Complete | CreateBusiness, context tests | `foundation-v1-checkpoint` |
| Core | Branch foundation | ✅ DONE | Yes | Branch model/default branch and scoping | — | — | P0 | Complete | ProductBranchSettingsTest | `foundation-v1-checkpoint` |
| Core | Auth foundation | ✅ DONE | Yes | Stateful Sanctum session login/logout/me with CSRF and rotation; no browser bearer token | Password Reset tracked under Security | — | P0 | Complete | AuthenticatedSessionTest | `b8ecab5` |
| Core | RBAC | ✅ DONE | Yes | Business roles/permissions and middleware | Extend only with approved workflows | — | P0 | Complete | ProvisionFoundationRbacTest, route permission matrix | `b8ecab5` |
| Core | Godfather | ✅ DONE | Yes | Env bootstrap, Gate bypass, Business invisibility, rotation | Operational credential custody | Production owner | P0 | Complete | GodfatherAccessTest | `foundation-v1-checkpoint` |
| Core | Audit | ✅ DONE | Yes | Append-only audit with recursive secret redaction | Retention policy before Production | Commercial TBD | P1 | Pre-Staging | AuditRecorderTest | `foundation-v1-checkpoint` |
| Core | Instance/version metadata | ✅ DONE | Yes | Local informational metadata, no enforcement | Surface version in final operations view if needed | — | P2 | Complete | InstanceMetadataTest | `foundation-v1-checkpoint` |
| Core | Module foundation | ✅ DONE | Yes | Bundled registry/state/contracts | Runtime installer remains deferred | — | P2 | Complete | Module registry/state tests | `foundation-v1-checkpoint` |
| Core | Event contracts | ✅ DONE | Yes | Versioned event envelope | Add new events only with features | — | P2 | Complete | EventEnvelopeTest | `foundation-v1-checkpoint` |

### Localization

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Localization | Persian (`fa`) | ✅ DONE | Yes | Default locale on canonical unprefixed public URLs | Final Denardi copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | URL normalization checkpoint |
| Localization | English (`en`) | ✅ DONE | Yes | Independent `/en/...` routing/data fields | Final Denardi copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | URL normalization checkpoint |
| Localization | Arabic (`ar`) | ✅ DONE | Yes | Actual V1 `/ar/...` routing/data fields | Final Arabic copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | URL normalization checkpoint |
| Localization | RTL / LTR | ✅ DONE | Yes | Direction exclusively from locale metadata | Full breakpoint visual QA | — | P1 | Release QA | LocaleRegistryTest | `3bf46e3` |
| Localization | Translation validation | ✅ DONE | Yes | CMS/Menu readiness and explicit locale rows | UAT final copy | — | P0 | Content/UAT | LocalizedCmsTest | `63cbb4f` |
| Localization | Fallback behavior | ✅ DONE | Yes | No silent public language mixing | Revalidate with final content | — | P0 | Content/UAT | no-fallback tests | `3bf46e3` |

### Public Website

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Public | Home | 🟡 PARTIAL | Yes | Localized CMS-driven landing shell | Final blocks/copy/assets and approval | Denardi content | P0 | Content/UAT | LocalizedPublicPagesTest | `7d73f70` |
| Public | About | 🟡 PARTIAL | Yes | Localized route/page | Final approved copy/images | Denardi content | P1 | Content/UAT | LocalizedPublicPagesTest | `7d73f70` |
| Public | Contact | 🟡 PARTIAL | Yes | Localized route/page | Final address/phone/map/social/hours | Denardi content | P0 | Content/UAT | LocalizedPublicPagesTest | `7d73f70` |
| Public | Privacy | 🟡 PARTIAL | Yes | Published localized page shell | Legal/final privacy copy approval | Denardi approval | P1 | Content/UAT | provisioning test | `7d73f70` |
| Public | Responsive layout | ✅ DONE | Yes | Responsive public shell/menu/navigation verified at 320/375/390/430/768/1440 without horizontal overflow | Physical-device UAT | Device access | P0 | UAT | Browser viewport matrix | UI/UX checkpoint |
| Public | Denardi theme | 🟡 PARTIAL | Yes | Charcoal/Teal/Blue visual direction | Final brand assets and approved refinement | Brand assets | P1 | Content/UAT | visual smoke only | `2a13cbc` |
| Public | Footer / contact info | 🔴 BLOCKED | Yes | Footer contract/components exist | Populate real contact/map/social/hours | Denardi data | P0 | Content/UAT | — | `2a13cbc` |

### CMS

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| CMS | Pages | ✅ DONE | Yes | Structured Page model/Admin listing | Final content population | — | P0 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Blocks | ✅ DONE | Yes | Controlled typed block schemas | Final content population | — | P0 | Complete | BlockSchemaTest | `63cbb4f` |
| CMS | Create | ✅ DONE | Yes | Page and Block creation | — | — | P0 | Complete | LocalizedCmsTest | `3bf46e3` |
| CMS | Edit | ✅ DONE | Yes | Page and Block update with audit | — | — | P0 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Delete | ✅ DONE | Yes | Safe Page archive/delete and Block delete | — | — | P0 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Reorder | ✅ DONE | Yes | Safe complete-set Block ordering | — | — | P1 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Enable / Disable | ✅ DONE | Yes | Block state and published filtering | — | — | P1 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Draft | ✅ DONE | Yes | Independent draft state/current revision | — | — | P0 | Complete | LocalizedCmsTest | `3bf46e3` |
| CMS | Preview | ✅ DONE | Yes | Authenticated, private/no-store, noindex preview | — | — | P0 | Complete | CmsManagementTest | `047830a` |
| CMS | Publish | ✅ DONE | Yes | Readiness gate and immutable snapshot | — | — | P0 | Complete | CmsManagementTest | `63cbb4f` |
| CMS | Localized content | ✅ DONE | Yes | Explicit FA/EN/AR translation rows/content schemas | Final copy | — | P0 | Content/UAT | LocalizedCmsTest | `63cbb4f` |

### Media

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Media | Secure upload | ✅ DONE | Yes | MIME/image/decode validation, disguised-payload rejection and Business storage | Production upload-volume tuning only | — | P0 | Complete | MediaManagementTest | `b8ecab5` |
| Media | Thumbnails | ✅ DONE | Yes | 480px WebP thumbnail | Final crop visual QA | — | P1 | Content/UAT | MediaManagementTest | `63cbb4f` |
| Media | WebP optimization | ✅ DONE | Yes | Optimized WebP up to 1600px | Tune only if real assets require | — | P1 | Complete | MediaManagementTest | `63cbb4f` |
| Media | Preview/listing | ✅ DONE | Yes | Practical thumbnail grid and metadata edit | — | — | P1 | Complete | AdminShellTest | `63cbb4f` |
| Media | Product assignment | ✅ DONE | Yes | Business-scoped primary image assignment | Final assets | — | P0 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Media | CMS assignment | ✅ DONE | Yes | Media selectors and published snapshot metadata | Final assets | — | P0 | Content/UAT | CmsManagementTest | `63cbb4f` |
| Media | Alt/title localization | ✅ DONE | Yes | FA/EN/AR metadata | Final copy | — | P0 | Content/UAT | MediaManagementTest | `63cbb4f` |
| Media | In-use protection | ✅ DONE | Yes | Product/CMS references shown; delete blocked | — | — | P0 | Complete | MediaManagementTest | `63cbb4f` |

### Digital Menu

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Menu | Categories | ✅ DONE | Yes | Localized CRUD/archive/hierarchy | Final menu import | — | P0 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Menu | Products | ✅ DONE | Yes | Business catalog CRUD/archive | Final menu import | — | P0 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Menu | Variants / Add-ons UI | ⚪ OUT OF V1 | No | Future-safe contracts only | Implement only if signed final menu requires | Signed requirement | P2 | Future/change request | — | architecture spec |
| Menu | Branch price | ✅ DONE | Yes | Independent IRR price + version check | Final prices/UAT | — | P0 | Content/UAT | ProductBranchSettingsTest | `63cbb4f` |
| Menu | Availability | ✅ DONE | Yes | Branch availability independent from publication | UAT | — | P0 | Content/UAT | ProductBranchSettingsTest | `63cbb4f` |
| Menu | Sold out | ✅ DONE | Yes | Reversible `available/sold_out` | UAT | — | P0 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Menu | Search | ✅ DONE | Yes | Current-locale title search | Device/final-data QA | — | P1 | Release QA | public menu tests | `7d73f70` |
| Menu | Category ordering | ✅ DONE | Yes | Admin reorder API/UI | UAT | — | P1 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Menu | Product ordering | ✅ DONE | Yes | Per-category reorder API/UI | UAT | — | P1 | Content/UAT | MenuManagementTest | `63cbb4f` |
| Menu | Product images | ✅ DONE | Yes | Admin assignment and public optimized render | Final images | — | P1 | Content/UAT | Menu/Media tests | `63cbb4f` |
| Menu | Publish states | ✅ DONE | Yes | draft/published/inactive/archived visibility | Final lifecycle UAT | — | P0 | Content/UAT | ProductBranchSettingsTest | `63cbb4f` |

### QR

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| QR | General QR | ✅ DONE | Yes | Stable public menu redirect | Physical proof | Final print input | P0 | UAT | QrRedirectAnalyticsTest | `bc8c23a` |
| QR | Table QR | ✅ DONE | Yes | Stable per-table attribution without ordering | Final table list/physical proof | Table list | P0 | UAT | QrRedirectAnalyticsTest | `bc8c23a` |
| QR | Stable routes | ✅ DONE | Yes | Public IDs and canonical redirect | — | — | P0 | Complete | QR tests | `bc8c23a` |
| QR | SVG | ✅ DONE | Yes | Downloadable artwork | Print proof | — | P1 | UAT | QrArtworkTest | `2b603e8` |
| QR | PNG | ✅ DONE | Yes | Downloadable artwork | Print proof | — | P1 | UAT | QrArtworkTest | `2b603e8` |
| QR | PDF | ✅ DONE | Yes | Downloadable artwork | Print proof | — | P1 | UAT | QrArtworkTest | `2b603e8` |
| QR | Scan attribution | ✅ DONE | Yes | QR/table/locale/device event context | Real traffic QA | — | P0 | Staging/UAT | QrRedirectAnalyticsTest | `bc8c23a` |
| QR | Deduplication | ✅ DONE | Yes | 30-minute fingerprint window | Real traffic QA | — | P0 | Staging/UAT | QrRedirectAnalyticsTest | `bc8c23a` |

### Analytics

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Analytics | Menu View | ✅ DONE | Yes | Public menu records event | Real traffic QA | — | P1 | Staging/UAT | Analytics tests | `bc8c23a` |
| Analytics | QR Scan | ✅ DONE | Yes | Redirect records deduplicated scan | Real traffic QA | — | P0 | Staging/UAT | QR analytics tests | `bc8c23a` |
| Analytics | Category View | ✅ DONE | Yes | Intersection-based public event, validation, branch context and 30-minute deduplication | Real traffic QA | — | P1 | Staging/UAT | PublicMenuAnalyticsTest | `8519229` |
| Analytics | Product View | ✅ DONE | Yes | Intersection-based public event, validation, branch context and 30-minute deduplication | Real traffic QA | — | P1 | Staging/UAT | PublicMenuAnalyticsTest | `8519229` |
| Analytics | Bot filtering | ✅ DONE | Yes | Known-bot exclusion | Staging verification | — | P1 | Staging/UAT | QR analytics tests | `bc8c23a` |
| Analytics | Device/table breakdown | ✅ DONE | Yes | Business-scoped 30-day device and Table QR query/UI | Real-traffic QA | — | P2 | Complete | AnalyticsSummaryTest | `b8ecab5` |
| Analytics | Dashboard visibility | ✅ DONE | Yes | Today/7/30-day event totals | UAT | — | P1 | Content/UAT | AnalyticsSummaryTest | `bc8c23a` |

### Admin

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Admin | Overview | ✅ DONE | Yes | Metrics/readiness/backup summary | Final data UAT | — | P1 | UAT | AdminShellTest | `63cbb4f` |
| Admin | Product management | ✅ DONE | Yes | CRUD/lifecycle/image/branch setting flows | Owner UAT | — | P0 | UAT | MenuManagementTest | `63cbb4f` |
| Admin | Category management | ✅ DONE | Yes | CRUD/archive/order/parent flows | Owner UAT | — | P0 | UAT | MenuManagementTest | `63cbb4f` |
| Admin | CMS | ✅ DONE | Yes | Page/Block workflow | Editor UAT | — | P0 | UAT | CMS tests | `63cbb4f` |
| Admin | Media | ✅ DONE | Yes | Upload/list/edit/reference/delete UI | Editor UAT | — | P1 | UAT | Media tests | `63cbb4f` |
| Admin | QR | ✅ DONE | Yes | General/Table creation and artwork | Final tables/print QA | — | P1 | UAT | QR tests | `2b603e8` |
| Admin | Analytics | ✅ DONE | Yes | Today/7/30 totals plus 30-day device/Table QR breakdown | Real-data UAT | — | P2 | Content/UAT | AnalyticsSummaryTest | `b8ecab5` |
| Admin | Localization | ✅ DONE | Yes | Metadata/readiness and independent editors | Final content UAT | — | P0 | UAT | AdminShellTest | `63cbb4f` |
| Admin | Mobile usability | ✅ DONE | Yes | Collapsible mobile navigation, responsive forms/cards, loading/retry and accessible status feedback | Physical-device UAT | Device access | P0 | UAT | Browser viewport matrix | UI/UX checkpoint |
| Admin | User list/status | ✅ DONE | Yes | Business-scoped list with account status; Godfather/protected Lamatech identities excluded | Owner UAT | — | P0 | Complete locally | UserManagementControllerTest | `2d7a465` |
| Admin | User create/invite | ✅ DONE | Yes | Name/username/email creation with random internal credential and secure setup-link request | Production mail delivery | Infrastructure | P0 | Staging | UserManagementControllerTest | `2d7a465` |
| Admin | User profile edit | ✅ DONE | Yes | Validated name, username and email updates in `Settings → Users & Access` | Owner UAT | — | P1 | Complete locally | UserManagementControllerTest | `2d7a465` |
| Admin | Role assignment | ✅ DONE | Yes | Existing Business Owner/Content Editor roles only; no raw permissions or Lamatech escalation | Owner UAT | — | P0 | Complete locally | UserManagementControllerTest | `2d7a465` |
| Admin | Access activation | ✅ DONE | Yes | Active/inactive state, login guard and target session/token revocation | Owner UAT | — | P0 | Complete locally | UserManagementControllerTest | `2d7a465` |
| Admin | Managed Password Reset | ✅ DONE | Yes | Active scoped users receive throttled secure reset request; no password is displayed | Production mail delivery | Infrastructure | P0 | Staging | UserManagementControllerTest, PasswordResetControllerTest | `2d7a465` |
| Admin | Safe deactivation | ✅ DONE | Yes | Delete action deactivates without erasing history; self and final active owner lockout prevented | Owner UAT | — | P0 | Complete locally | UserManagementControllerTest | `2d7a465` |
| Admin | User access audit/isolation | ✅ DONE | Yes | Create/update/deactivate/reset events audited; cross-Business, Godfather and Lamatech isolation enforced | Operational audit review | — | P0 | Complete locally | UserManagementControllerTest | `2d7a465` |

### SEO

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| SEO | Canonical | ✅ DONE | Yes | Persian unprefixed canonical plus `/en` and `/ar` localized canonical links; legacy `/fa` uses 301 | Production URL validation | Domain | P0 | Staging | PublicSeoAndPwaTest | URL normalization checkpoint |
| SEO | Hreflang | ✅ DONE | Yes | `fa/en/ar` and x-default | Production crawl validation | Domain | P0 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Sitemap | ✅ DONE | Yes | Localized public URLs | Final readiness/crawl validation | — | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Robots | ✅ DONE | Yes | Public allow/Admin disallow | Production validation | Domain | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | OpenGraph | ✅ DONE | Yes | Localized title/description/url | Final metadata/assets | Denardi content | P1 | Content/UAT | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Structured data | ✅ DONE | Yes | Locale-aware Cafe/LocalBusiness JSON-LD from published CMS/config data; no fake business values | Populate final verified business data | Denardi content | P1 | Content/UAT | PublicSeoAndPwaTest | `8519229` |
| SEO | Metadata | 🟡 PARTIAL | Yes | CMS localized metadata architecture | Final approved titles/descriptions | Denardi content | P0 | Content/UAT | CMS/SEO tests | `63cbb4f` |
| SEO | Lighthouse readiness | 🟡 PARTIAL | Yes | Local FA/EN/AR Home and Menu baseline: SEO 92/92/92/100; missing Home descriptions correctly remain content-dependent | Repeat against final metadata/domain in Production-like Staging | Staging/content | P0 | Staging/UAT | Lighthouse local baseline | `b8ecab5` |

### PWA

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| PWA | Manifest | ✅ DONE | Yes | Public/Admin manifests | Final Production validation | — | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| PWA | Icons | ✅ DONE | Yes | 192/512 PNG, dedicated maskable 512, Apple touch icon and SVG fallback | Replace art only if final logo changes | Final logo | P1 | Content/UAT | PublicSeoAndPwaTest | `8519229` |
| PWA | Installability | ✅ DONE | Yes | Public/Admin manifests, required icons, scope/start URL and service worker prerequisites verified | Real device install proof | Staging/device | P1 | Staging/UAT | PublicSeoAndPwaTest | `8519229` |
| PWA | Offline shell | ✅ DONE | Yes | Public fallback shell | Device behavior QA | — | P2 | Release QA | PWA test | `2a13cbc` |
| PWA | Public caching | ✅ DONE | Yes | Online-first navigation cache | Cache/version update QA | — | P1 | Release QA | PWA test | `2a13cbc` |
| PWA | Admin behavior | ✅ DONE | Yes | Admin remains online-only; API traffic bypasses service-worker cache and no mutation queue exists | Device error-message QA | — | P1 | Release QA | PublicSeoAndPwaTest | `8519229` |
| PWA | No offline editing/sync | ✅ DONE | Yes | No offline mutation/synchronization feature exists | Preserve this constraint | — | P0 | Complete | architecture review | `2a13cbc` |

### Security

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Security | Login / logout | ✅ DONE | Yes | Stateful Sanctum session, CSRF cookie/header, session rotation/invalidation, HttpOnly cookie and no localStorage token | Production Secure-cookie/TLS validation | Staging | P0 | Staging | AuthenticatedSessionTest, Playwright | `b8ecab5` |
| Security | Password Reset | ✅ DONE | Yes | Generic-response API/UI flow, expiring tokens, strong validation, throttling, session revocation and audit | Configure Production mail provider | Infrastructure | P0 | Staging | PasswordResetControllerTest | `8519229` |
| Security | RBAC | ✅ DONE | Yes | Permission middleware and roles | Full role matrix | — | P0 | Release QA | RBAC tests | Foundation |
| Security | Business isolation | ✅ DONE | Yes | Business-scoped controllers/actions | Broader IDOR matrix | — | P0 | Release QA | CMS/QR/context tests | `63cbb4f` |
| Security | Godfather isolation | ✅ DONE | Yes | Invisible/protected highest access | Operational custody review | Credential owner | P0 | Pre-Staging | Godfather tests | Foundation |
| Security | Rate limiting | ✅ DONE | Yes | Login, Password Reset and public analytics limiters | Tune only from measured Staging traffic | — | P0 | Complete | Auth/analytics tests | `8519229` |
| Security | Input validation | ✅ DONE | Yes | Form Requests and block schema validation | Security fuzz cases | — | P0 | Release QA | feature tests | `63cbb4f` |
| Security | Dependency audit | ✅ DONE | Yes | Composer/npm audits in CI | Continue every checkpoint | — | P0 | Continuous | CI | `f493edf` |
| Security | Production 2FA | ⚪ OUT OF V1 | No | Explicitly deferred by approved amendment | New scope required | Signed change | P2 | Future | — | architecture spec |
| Security | Release security review | ✅ DONE | Yes | XSS escaping, cross-Business IDOR, route permissions, CSRF/session fixation/logout, upload fuzzing, baseline headers, dependency and tracked-secret scans pass | Repeat environment-dependent checks in Staging | Staging | P0 | Complete locally | ReleaseSecurityHeadersTest and feature matrix | `b8ecab5` |

### Backup / Operations

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Backup | DB backup | ✅ DONE | Yes | MySQL dump + gzip record lifecycle | Production execution | Infrastructure | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Full backup | ✅ DONE | Yes | DB/uploads/non-secret manifest archive | Production execution | Infrastructure | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Secret exclusion | ✅ DONE | Yes | `.env`/keys/secrets excluded; manifest references only | Escrow procedure exercise | Secret owner | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Checksum verification | ✅ DONE | Yes | SHA-256 verification/audit | Automated post-backup policy | — | P1 | Pre-Staging | BackupLifecycleTest | `cd48495` |
| Backup | Scheduler | ✅ DONE | Yes | Daily DB / weekly Full schedule | Verify cron heartbeat in environment | Hosting | P0 | Staging | schedule review | `cd48495` |
| Backup | Restore implementation | ✅ DONE | Yes | Guarded CLI preflight/execution, checksum/manifest/instance checks, safety backup, maintenance/confirmation/lock, streaming archive limits, DB/uploads restore and audit | Execute only through Staging drill before Production trust | — | P0 | Complete | BackupLifecycleTest | `22ab386` |
| Backup | Restore drill | ⏳ NOT STARTED | Yes | Acceptance checklist documented | Execute and record full Staging drill | Staging/offsite | P0 | Staging/UAT | None | — |
| Backup | Offsite encrypted storage | 🔴 BLOCKED | Yes | Production policy rejects unsafe storage | Select/configure encrypted external destination | Infrastructure owner | P0 | Staging | policy test | `cd48495` |

### Health / Monitoring

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Health | Application | ✅ DONE | Yes | Authenticated application health result | External probe | Monitoring access | P0 | Staging | HealthControllerTest | Foundation |
| Health | Database | ✅ DONE | Yes | Live `SELECT 1` check | External alert | Monitoring access | P0 | Staging | HealthControllerTest | Foundation |
| Health | Storage | ✅ DONE | Yes | Active-disk write/read/delete probe with safe cleanup | External alert wiring | Monitoring access | P1 | Staging | HealthControllerTest | `8519229` |
| Health | Queue | ✅ DONE | Yes | Configured queue connection visibility and backlog access check | Threshold/alert tuning | Monitoring access | P1 | Staging | HealthControllerTest | `8519229` |
| Health | Scheduler | ✅ DONE | Yes | Every-minute persistent heartbeat with stale/missing states | Verify environment cron/worker | Hosting | P0 | Staging | HealthControllerTest | `8519229` |
| Health | Backup freshness | ✅ DONE | Yes | missing/stale/unverified/ok result with Production health severity | Production alert wiring | Monitoring access | P0 | Staging | HealthControllerTest | `8519229` |
| Health | External monitoring | ⏳ NOT STARTED | Yes | Requirements documented | Uptime/TLS/error/disk/queue/scheduler/backup alerts | Monitoring owner | P0 | Staging | None | — |

### QA

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| QA | PHPUnit | ✅ DONE | Yes | 109 tests / 735 assertions pass locally | Grow with features | — | P0 | Continuous | Full suite | Reference-led menu checkpoint |
| QA | PHPStan / Larastan | ✅ DONE | Yes | Zero errors | Maintain | — | P0 | Continuous | CI | `f493edf` |
| QA | Pint | ✅ DONE | Yes | Formatting gate passes | Maintain | — | P0 | Continuous | CI | `f493edf` |
| QA | Frontend tests | ✅ DONE | Yes | 8 Node tests cover Admin data helpers and public localized search | Grow with features | — | P1 | Continuous | frontend helper tests | UI/UX checkpoint |
| QA | Production build | ✅ DONE | Yes | Vite build passes | Staging artifact check | — | P0 | Continuous | CI | `f493edf` |
| QA | Browser smoke tests | ✅ DONE | Yes | FA/EN/AR public flows plus authenticated User Access list/create dialog smoke-tested without application console errors | Full P0 automation | — | P1 | Release QA | Playwright smoke | `2d7a465` |
| QA | FA/EN/AR tests | ✅ DONE | Yes | Routing/content/direction/readiness tested | Final copy/UAT | — | P0 | Content/UAT | Locale/Public/CMS tests | `63cbb4f` |
| QA | Responsive tests | ✅ DONE | Yes | Public FA/EN/AR and authenticated Admin verified at 320/375/390/430/768/1440 with no horizontal overflow | Physical devices | Device access | P0 | UAT | In-app browser matrix | UI/UX checkpoint |
| QA | Accessibility | 🟡 PARTIAL | Yes | Semantic browser matrix plus local Lighthouse accessibility 100 on FA/EN/AR Home and Persian Menu | axe and screen-reader verification | Staging/device | P0 | Staging/UAT | Semantic browser/Lighthouse inspection | `b8ecab5` |
| QA | Lighthouse | 🟡 PARTIAL | Yes | Local FA/EN/AR Home and Persian Menu: performance 100/99/100/98, accessibility 100, best-practices 100, SEO 92/92/92/100 | Repeat with final content/assets/domain in Production-like Staging | Staging/content | P0 | Staging/UAT | Lighthouse local baseline | `b8ecab5` |
| QA | iOS Safari | ⏳ NOT STARTED | Yes | — | Last two major versions P0 flows | Device access | P0 | Staging/UAT | None | — |
| QA | Android Chrome | ⏳ NOT STARTED | Yes | — | Last two major versions P0 flows | Device access | P0 | Staging/UAT | None | — |

### Deployment

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Deployment | Local | ✅ DONE | Yes | MySQL app, migrations, demo data and local server | Maintain setup guide | — | P0 | Complete | Provision test | `7d73f70` |
| Deployment | CI | ✅ DONE | Yes | Disposable MySQL 8.4 pipeline and failure annotations | Maintain green | — | P0 | Continuous | Run 33568053762 | `247596c` |
| Deployment | Staging | ⏳ NOT STARTED | Yes | Runbook/spec exists | Provision Production-like environment | Hosting/access | P0 | Staging | None | — |
| Deployment | Production secrets | 🔴 BLOCKED | Yes | Secret exclusion/recovery contract | Securely provision environment/escrow | Secret owner/access | P0 | Production | policy tests | — |
| Deployment | SSL / domain | 🔴 BLOCKED | Yes | Routing/SEO contracts exist | Domain, DNS and TLS configuration | Denardi/access | P0 | Production | None | — |
| Deployment | Production deployment | ⏳ NOT STARTED | Yes | Artifact/release flow documented | Execute release and observation | Staging sign-off | P0 | Production | None | — |
| Deployment | UAT | 🔴 BLOCKED | Yes | Checklist exists | Content/device/Owner approval | Denardi approver/content | P0 | Staging/UAT | None | — |
| Deployment | Rollback readiness | 🟡 PARTIAL | Yes | Rollback policy documented | Rehearse code/data/DNS rollback | Staging | P0 | Staging/UAT | None | — |

### Real Denardi Content

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Content | Logo / brand assets | 🔴 BLOCKED | Yes | Placeholder/current visual shell | Receive licensed final files | Denardi | P0 | Content/UAT | Visual QA pending | — |
| Content | Final images | 🔴 BLOCKED | Yes | Media pipeline ready | Receive licensed final images | Denardi | P0 | Content/UAT | Media QA pending | — |
| Content | Final menu | 🔴 BLOCKED | Yes | Demo-only menu exists | Receive/import approved menu | Denardi | P0 | Content/UAT | UAT pending | — |
| Content | Final prices | 🔴 BLOCKED | Yes | Branch price workflow ready | Receive/approve prices | Denardi | P0 | Content/UAT | UAT pending | — |
| Content | Final translations | 🔴 BLOCKED | Yes | FA/EN/AR architecture ready | Approved copy for required content | Denardi | P0 | Content/UAT | readiness/UAT | — |
| Content | Address | 🔴 BLOCKED | Yes | CMS field/page ready | Final address/hours | Denardi | P0 | Content/UAT | UAT pending | — |
| Content | Map | 🔴 BLOCKED | Yes | Location block contract exists | Final map pin/URL | Denardi | P0 | Content/UAT | UAT pending | — |
| Content | Phone | 🔴 BLOCKED | Yes | Contact content ready | Final public number | Denardi | P0 | Content/UAT | UAT pending | — |
| Content | Instagram / social | 🔴 BLOCKED | Yes | Link fields ready | Final approved URLs | Denardi | P1 | Content/UAT | UAT pending | — |
| Content | Table numbers | 🔴 BLOCKED | Yes | Table QR engine ready | Final identifiers/count/stand mapping | Denardi | P0 | Content/UAT | physical QA pending | — |
| Content | Approver confirmation | 🔴 BLOCKED | Yes | UAT checklist ready | Named approver and written sign-off | Denardi | P0 | Staging/UAT | UAT pending | — |

### Future / Out of V1

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Future | Campaign QR | ⚪ OUT OF V1 | No | Explicit rejection/defer contract | Signed change request required | Signed requirement | P2 | Future | QR rejection test | `2b603e8` |
| Future | Ordering | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | Payment | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | Reservation | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | CRM | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | Loyalty | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | Inventory | ⚪ OUT OF V1 | No | Nothing operational implemented | Future scope | — | P2 | Future | — | — |
| Future | Operational AI | ⚪ OUT OF V1 | No | Contracts/flags only | Separate future authorization | — | P2 | Future | — | architecture spec |
| Future | Control Plane | ⚪ OUT OF V1 | No | Local instance metadata only | Separate product phase | — | P2 | Future | — | architecture spec |
| Future | Multi-tenant SaaS | ⚪ OUT OF V1 | No | Single-instance Business boundary | Separate architecture/product phase | — | P2 | Future | — | architecture spec |

## Git / CI Evidence

- Branch: `develop/denardi-v1`
- Feature checkpoint commit: `2f59efb` (reference-led menu / local demo presentation)
- Menu checkpoint CI: [33732532543](https://github.com/lamatech-dev/LamaFood/actions/runs/33732532543) passed on disposable MySQL 8.4, including Composer validation, bootstrap and clean migrations.
- User Management CI: [33664462499](https://github.com/lamatech-dev/LamaFood/actions/runs/33664462499) passed on disposable MySQL 8.4
- Release QA CI: [33661676730](https://github.com/lamatech-dev/LamaFood/actions/runs/33661676730) on disposable MySQL 8.4
- Key commits: `2d7a465` business User Management; `b8ecab5` Release QA hardening; `22ab386` guarded Safe Restore; `6ccc4e1` Persian URL normalization; `12659c9` UI/UX checkpoint; `8519229` Current Phase completion; `63cbb4f` management completion; `259a348` persistent tracker/dashboard
- Test summary: 107 PHPUnit tests / 708 assertions; 8 frontend tests; PHPStan zero errors; Pint/build and Composer/npm validation/audits pass
- Secrets: ignored `.env` remains untracked; tracked examples contain placeholders only

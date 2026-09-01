# Denardi V1 Project Tracker

| Project | Branch | V1 Completion | Go-live Readiness | Last updated | Last reviewed commit | CI status | Current blockers |
|---|---|---:|---:|---|---|---|---:|
| Denardi V1 | `develop/denardi-v1` | **82%** | **55%** | 2026-09-02 | `247596c` | ✅ [Run 33568053762](https://github.com/lamatech-dev/LamaFood/actions/runs/33568053762) | **3 blocker groups** |

This Markdown file is the canonical project-status document. Percentages are evidence-based planning estimates, not mathematical precision. The tracker contains **159 control items**: 99 Done, 14 Partial, 18 Not Started, 16 Blocked line items and 12 Out of V1. The blocked line items roll up into three actual external blocker groups.

## Maintenance rule

After every major implementation phase or release checkpoint, `DENARDI_V1_TRACKER.md` and `DENARDI_V1_TRACKER.html` must be updated together to reflect the actual repository state. The same checkpoint must update `docs/IMPLEMENTATION_LOG.md`, be committed on `develop/denardi-v1`, pushed to GitHub and pass CI before it is reported complete. Never record secrets, passwords or production credentials.

## Status legend

- ✅ **DONE** — implemented and backed by code/test/CI evidence
- 🟡 **PARTIAL** — useful implementation exists, but required work or final verification remains
- ⏳ **NOT STARTED** — required V1 work has not started
- 🔴 **BLOCKED** — cannot close without external input/access/approval
- ⚪ **OUT OF V1** — explicitly deferred and not counted as incomplete V1 work

## Current State

Foundation, three-language localization, CMS, Media, Product/Category management, branch pricing/availability, General/Table QR, public menu, basic analytics, SEO/PWA foundations and secret-safe backup creation are implemented. The full local suite and GitHub Actions pass on MySQL 8.4.11. The largest remaining engineering items are Password Reset, missing Category/Product analytics emission, structured data, complete health checks and release-grade QA. Go-live additionally depends on real Denardi content, Staging, external encrypted backups, restore drill, monitoring, UAT and Production access.

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
- 65 PHPUnit tests / 315 assertions, PHPStan, Pint, frontend tests/build and dependency audits

## Remaining for V1

- Password Reset flow and tests
- Actual Category View and Product View analytics emission
- Structured data (`CafeOrCoffeeShop`/`LocalBusiness`) from real business data
- Complete storage/queue/scheduler health checks
- Production-size PWA icons and installability/device validation
- Final mobile Admin usability/error-state refinement
- Release-grade responsive, accessibility, Lighthouse, iOS Safari and Android QA
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

1. Implement Password Reset and the missing required event/health gaps with tests.
2. Complete structured data, PWA production icons and Admin error/mobile polish.
3. Run security, accessibility, responsive and browser automation; fix P0/P1 findings.
4. Receive and import final `fa/en/ar` Denardi content, menu, images and table list.
5. Provision Staging only after explicit environment/credential authorization.
6. Configure offsite encrypted backup/monitoring and complete restore/rollback drills.
7. Run Denardi UAT, physical QR proof, training and written sign-off.
8. Perform Production deploy, DNS/SSL switch, smoke checks and observation window.

## Completion Metrics

| Metric | Estimate | Basis |
|---|---:|---|
| Foundation | 100% | Core, migrations, RBAC, metadata, module/event contracts and CI are green |
| Functional V1 | 86% | Primary CMS/Menu/Media/QR flows work; Password Reset and analytics/health gaps remain |
| UI/UX readiness | 75% | Functional responsive shell exists; final visual/content/device UAT remains |
| QA readiness | 62% | Automated quality gates pass; full browser/accessibility/performance/security matrix remains |
| Production readiness | 30% | Runbooks and backup contracts exist; Staging/offsite/monitoring/Production are not configured |
| Overall V1 completion | **82%** | Weighted delivery estimate based on implemented V1 features |
| Go-live readiness | **55%** | Technical implementation is ahead of content, operations and approval readiness |

## Feature Tracker

Columns: **V1** means required for Denardi V1. Priority uses P0 (release-blocking), P1 (required), P2 (polish/future hardening).

### Foundation / Core

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Core | Laravel / PHP runtime | ✅ DONE | Yes | Laravel 13.29 / PHP 8.4 policy and lockfiles | Maintain pins | — | P0 | Complete | CI bootstrap | `foundation-v1-checkpoint` |
| Core | MySQL / migrations | ✅ DONE | Yes | MySQL 8.4 contract; from-zero CI migrations | Maintain migration safety | — | P0 | Complete | Full CI | `foundation-v1-checkpoint` |
| Core | Business context | ✅ DONE | Yes | Single-instance Business scope and resolver | — | — | P0 | Complete | CreateBusiness, context tests | `foundation-v1-checkpoint` |
| Core | Branch foundation | ✅ DONE | Yes | Branch model/default branch and scoping | — | — | P0 | Complete | ProductBranchSettingsTest | `foundation-v1-checkpoint` |
| Core | Auth foundation | ✅ DONE | Yes | Sanctum login/logout/me | Password Reset tracked under Security | — | P0 | Complete | AuthenticatedSessionTest | `foundation-v1-checkpoint` |
| Core | RBAC | ✅ DONE | Yes | Business roles/permissions and middleware | Final security matrix | — | P0 | Complete | ProvisionFoundationRbacTest | `foundation-v1-checkpoint` |
| Core | Godfather | ✅ DONE | Yes | Env bootstrap, Gate bypass, Business invisibility, rotation | Operational credential custody | Production owner | P0 | Complete | GodfatherAccessTest | `foundation-v1-checkpoint` |
| Core | Audit | ✅ DONE | Yes | Append-only audit with recursive secret redaction | Retention policy before Production | Commercial TBD | P1 | Pre-Staging | AuditRecorderTest | `foundation-v1-checkpoint` |
| Core | Instance/version metadata | ✅ DONE | Yes | Local informational metadata, no enforcement | Surface version in final operations view if needed | — | P2 | Complete | InstanceMetadataTest | `foundation-v1-checkpoint` |
| Core | Module foundation | ✅ DONE | Yes | Bundled registry/state/contracts | Runtime installer remains deferred | — | P2 | Complete | Module registry/state tests | `foundation-v1-checkpoint` |
| Core | Event contracts | ✅ DONE | Yes | Versioned event envelope | Add new events only with features | — | P2 | Complete | EventEnvelopeTest | `foundation-v1-checkpoint` |

### Localization

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Localization | Persian (`fa`) | ✅ DONE | Yes | Default public/Admin content model | Final Denardi copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | `3bf46e3` |
| Localization | English (`en`) | ✅ DONE | Yes | Independent routing/data fields | Final Denardi copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | `3bf46e3` |
| Localization | Arabic (`ar`) | ✅ DONE | Yes | Actual V1 routing/data fields | Final Arabic copy | Content tracked separately | P0 | Content/UAT | Locale/Public tests | `3bf46e3` |
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
| Public | Responsive layout | 🟡 PARTIAL | Yes | Responsive CSS and mobile navigation | Required visual/device matrix | — | P0 | Release QA | Browser smoke only | `2a13cbc` |
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
| Media | Secure upload | ✅ DONE | Yes | MIME/image validation and Business storage | Security release fuzzing | — | P0 | Release QA | MediaManagementTest | `63cbb4f` |
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
| Analytics | Category View | ⏳ NOT STARTED | Yes | Enum/summary contract exists | Emit actual interaction/view event | — | P1 | Release readiness | No behavior test | `bc8c23a` |
| Analytics | Product View | ⏳ NOT STARTED | Yes | Enum/summary contract exists | Define interaction and emit event | — | P1 | Release readiness | No behavior test | `bc8c23a` |
| Analytics | Bot filtering | ✅ DONE | Yes | Known-bot exclusion | Staging verification | — | P1 | Staging/UAT | QR analytics tests | `bc8c23a` |
| Analytics | Device/table breakdown | 🟡 PARTIAL | Yes | Data is captured in event records | Admin breakdown UI/query | — | P2 | Release readiness | event tests | `bc8c23a` |
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
| Admin | Analytics | ✅ DONE | Yes | Basic totals view | Add required breakdown polish | — | P2 | Release readiness | AnalyticsSummaryTest | `bc8c23a` |
| Admin | Localization | ✅ DONE | Yes | Metadata/readiness and independent editors | Final content UAT | — | P0 | UAT | AdminShellTest | `63cbb4f` |
| Admin | Mobile usability | 🟡 PARTIAL | Yes | Responsive navigation/forms | iOS/Android flow matrix and error polish | — | P0 | Release QA | smoke only | `63cbb4f` |

### SEO

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| SEO | Canonical | ✅ DONE | Yes | Localized canonical links | Production URL validation | Domain | P0 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Hreflang | ✅ DONE | Yes | `fa/en/ar` and x-default | Production crawl validation | Domain | P0 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Sitemap | ✅ DONE | Yes | Localized public URLs | Final readiness/crawl validation | — | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Robots | ✅ DONE | Yes | Public allow/Admin disallow | Production validation | Domain | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | OpenGraph | ✅ DONE | Yes | Localized title/description/url | Final metadata/assets | Denardi content | P1 | Content/UAT | PublicSeoAndPwaTest | `2a13cbc` |
| SEO | Structured data | ⏳ NOT STARTED | Yes | Requirement documented | Implement real-data Cafe/LocalBusiness JSON-LD | Final business data | P1 | Release readiness | None | — |
| SEO | Metadata | 🟡 PARTIAL | Yes | CMS localized metadata architecture | Final approved titles/descriptions | Denardi content | P0 | Content/UAT | CMS/SEO tests | `63cbb4f` |
| SEO | Lighthouse readiness | ⏳ NOT STARTED | Yes | Targets documented | Run/fix Production-like audit | Staging | P0 | Staging/UAT | None | — |

### PWA

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| PWA | Manifest | ✅ DONE | Yes | Public/Admin manifests | Final Production validation | — | P1 | Staging | PublicSeoAndPwaTest | `2a13cbc` |
| PWA | Icons | 🟡 PARTIAL | Yes | SVG any/maskable icon | Production raster sizes and device QA | Final logo | P1 | Content/QA | manifest test | `2a13cbc` |
| PWA | Installability | ⏳ NOT STARTED | Yes | Manifest/SW prerequisites exist | Browser/device install tests | Staging/device | P1 | Staging/UAT | None | — |
| PWA | Offline shell | ✅ DONE | Yes | Public fallback shell | Device behavior QA | — | P2 | Release QA | PWA test | `2a13cbc` |
| PWA | Public caching | ✅ DONE | Yes | Online-first navigation cache | Cache/version update QA | — | P1 | Release QA | PWA test | `2a13cbc` |
| PWA | Admin behavior | 🟡 PARTIAL | Yes | Admin shell can load; APIs require network | Verify clear offline errors; no mutation queue | — | P1 | Release QA | None | `2a13cbc` |
| PWA | No offline editing/sync | ✅ DONE | Yes | No offline mutation/synchronization feature exists | Preserve this constraint | — | P0 | Complete | architecture review | `2a13cbc` |

### Security

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Security | Login / logout | ✅ DONE | Yes | Sanctum session endpoints/UI | Browser/session QA | — | P0 | Release QA | AuthenticatedSessionTest | Foundation |
| Security | Password Reset | ⏳ NOT STARTED | Yes | Laravel broker config only | API/UI/token/mail flow, throttle and tests | Mail/recovery decision | P0 | Next phase | None | — |
| Security | RBAC | ✅ DONE | Yes | Permission middleware and roles | Full role matrix | — | P0 | Release QA | RBAC tests | Foundation |
| Security | Business isolation | ✅ DONE | Yes | Business-scoped controllers/actions | Broader IDOR matrix | — | P0 | Release QA | CMS/QR/context tests | `63cbb4f` |
| Security | Godfather isolation | ✅ DONE | Yes | Invisible/protected highest access | Operational custody review | Credential owner | P0 | Pre-Staging | Godfather tests | Foundation |
| Security | Rate limiting | ✅ DONE | Yes | Login throttle | Add reset throttle with feature | — | P0 | Next phase | Auth test | Foundation |
| Security | Input validation | ✅ DONE | Yes | Form Requests and block schema validation | Security fuzz cases | — | P0 | Release QA | feature tests | `63cbb4f` |
| Security | Dependency audit | ✅ DONE | Yes | Composer/npm audits in CI | Continue every checkpoint | — | P0 | Continuous | CI | `f493edf` |
| Security | Production 2FA | ⚪ OUT OF V1 | No | Explicitly deferred by approved amendment | New scope required | Signed change | P2 | Future | — | architecture spec |
| Security | Release security review | 🟡 PARTIAL | Yes | Core authz/secret/upload tests exist | XSS/IDOR/session/stack/secret-scan matrix | — | P0 | Release QA | QA strategy | — |

### Backup / Operations

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Backup | DB backup | ✅ DONE | Yes | MySQL dump + gzip record lifecycle | Production execution | Infrastructure | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Full backup | ✅ DONE | Yes | DB/uploads/non-secret manifest archive | Production execution | Infrastructure | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Secret exclusion | ✅ DONE | Yes | `.env`/keys/secrets excluded; manifest references only | Escrow procedure exercise | Secret owner | P0 | Staging | BackupLifecycleTest | `cd48495` |
| Backup | Checksum verification | ✅ DONE | Yes | SHA-256 verification/audit | Automated post-backup policy | — | P1 | Pre-Staging | BackupLifecycleTest | `cd48495` |
| Backup | Scheduler | ✅ DONE | Yes | Daily DB / weekly Full schedule | Verify cron heartbeat in environment | Hosting | P0 | Staging | schedule review | `cd48495` |
| Backup | Restore implementation | ⏳ NOT STARTED | Yes | Recovery order documented | Safe restore command/runbook execution | Staging | P0 | Pre-Staging | None | — |
| Backup | Restore drill | ⏳ NOT STARTED | Yes | Acceptance checklist documented | Execute and record full Staging drill | Staging/offsite | P0 | Staging/UAT | None | — |
| Backup | Offsite encrypted storage | 🔴 BLOCKED | Yes | Production policy rejects unsafe storage | Select/configure encrypted external destination | Infrastructure owner | P0 | Staging | policy test | `cd48495` |

### Health / Monitoring

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| Health | Application | ✅ DONE | Yes | Authenticated application health result | External probe | Monitoring access | P0 | Staging | HealthControllerTest | Foundation |
| Health | Database | ✅ DONE | Yes | Live `SELECT 1` check | External alert | Monitoring access | P0 | Staging | HealthControllerTest | Foundation |
| Health | Storage | ⏳ NOT STARTED | Yes | — | Read/write health check | — | P1 | Release readiness | None | — |
| Health | Queue | ⏳ NOT STARTED | Yes | Queue foundation/config exists | Backlog/failed-job check | — | P1 | Release readiness | None | — |
| Health | Scheduler | ⏳ NOT STARTED | Yes | Schedule exists | Persistent heartbeat/freshness check | — | P0 | Release readiness | None | — |
| Health | Backup freshness | ✅ DONE | Yes | missing/stale/unverified/ok result | Production alert wiring | Monitoring access | P0 | Staging | HealthControllerTest | `cd48495` |
| Health | External monitoring | ⏳ NOT STARTED | Yes | Requirements documented | Uptime/TLS/error/disk/queue/scheduler/backup alerts | Monitoring owner | P0 | Staging | None | — |

### QA

| Area | Feature / Requirement | Status | V1 | What is completed | What remains | Blocker | Priority | Target phase | Related tests | Last relevant commit |
|---|---|---|---|---|---|---|---|---|---|---|
| QA | PHPUnit | ✅ DONE | Yes | 65 tests / 315 assertions pass | Grow with features | — | P0 | Continuous | Full suite | `047830a` |
| QA | PHPStan / Larastan | ✅ DONE | Yes | Zero errors | Maintain | — | P0 | Continuous | CI | `f493edf` |
| QA | Pint | ✅ DONE | Yes | Formatting gate passes | Maintain | — | P0 | Continuous | CI | `f493edf` |
| QA | Frontend tests | ✅ DONE | Yes | 6 Node tests pass | Add for new UI logic | — | P1 | Continuous | admin-data tests | `63cbb4f` |
| QA | Production build | ✅ DONE | Yes | Vite build passes | Staging artifact check | — | P0 | Continuous | CI | `f493edf` |
| QA | Browser smoke tests | ✅ DONE | Yes | FA/AR menu and Admin login smoke-tested | Full P0 automation | — | P1 | Release QA | Playwright smoke | `63cbb4f` |
| QA | FA/EN/AR tests | ✅ DONE | Yes | Routing/content/direction/readiness tested | Final copy/UAT | — | P0 | Content/UAT | Locale/Public/CMS tests | `63cbb4f` |
| QA | Responsive tests | 🟡 PARTIAL | Yes | Responsive CSS and basic smoke exist | 320/375/768/1024/1440 matrix | Devices/Staging | P0 | Release QA | None automated | — |
| QA | Accessibility | ⏳ NOT STARTED | Yes | Basic semantic/focus styles | axe, keyboard and screen-reader checks | — | P0 | Release QA | None | — |
| QA | Lighthouse | ⏳ NOT STARTED | Yes | Targets documented | Run/fix Production-like audit | Staging | P0 | Staging/UAT | None | — |
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
- Tracker baseline commit: `247596c`
- Latest green CI for reviewed state: [33568053762](https://github.com/lamatech-dev/LamaFood/actions/runs/33568053762)
- Key commits: `63cbb4f` management completion; `047830a` clean-CI preview isolation; `f493edf` completion log; `247596c` tracker baseline
- Test summary: 65 PHPUnit tests / 315 assertions; 6 frontend tests; PHPStan zero errors; Pint/build/audits pass
- Secrets: ignored `.env` remains untracked; tracked examples contain placeholders only

# Denardi V1 Implementation Log

This file records implementation checkpoints, verification evidence and remaining work. Meaningful implementation stages must be committed on `develop/denardi-v1`, pushed to GitHub and added here before the stage is treated as complete. Secrets and local credentials must never be recorded.

The canonical checklist is maintained in `docs/DENARDI_V1_TRACKER.md` and mirrored by the visual `docs/DENARDI_V1_TRACKER.html` dashboard; every future implementation checkpoint must update both in the same commit.

## 2026-09-03 — Shared premium mobile navigation

- Extracted the existing bottom navigation into one Blade partial used by every public Home/Menu/About/Contact/Privacy page. Localized links and current-page state are explicit; Privacy has no falsely highlighted destination. Existing Menu branch query is preserved on its dock link.
- Refined the dock as a floating, dark glass surface with restrained edge/depth, aqua active state, existing licensed icons, comfortable touch targets, safe-area spacing and reduced-motion/solid-surface fallbacks. Body bottom space is reserved on all public mobile pages; Admin and desktop navigation are unchanged.
- Browser checks: 45 combinations (five pages × three locales × 320/390/430px), no overflow, correct active state, all dock targets at least 44px and sufficient reserved bottom space. Desktop dock remains hidden. Actual Arabic Home/Menu dock clicks retained locale and updated the active state. No recent browser errors/warnings.
- Screenshots inspected: Home FA, Menu FA, About EN and Contact AR. Local evidence: `output/design-audit/after/dock-{menu-fa,about-en,contact-ar}-390.png`.
- Focused PHPUnit: 13 tests / 245 assertions; PHPStan zero errors, Pint, 10 frontend tests, Vite build and whitespace checks passed. New coverage verifies all 15 page/locale combinations and current navigation state. Completion estimates and external blockers unchanged.
- Full PHPUnit regression: **110 tests / 841 assertions passed**. GitHub CI for this checkpoint will be recorded after push.

## 2026-09-03 — English mobile composition amendment

- Per explicit review feedback, English mobile cards now place left-aligned text on the left and square media on the right. Persian/Arabic retain their approved image-left/text-right composition; desktop behavior is unchanged.
- CSS-only amendment. Browser checks at 320/390/430px across all three locales confirmed the intended side order, square media and no page overflow. English 390px screenshot updated in `output/design-audit/after/reference-menu-en-390.png`.
- Production build, 10 frontend tests and diff whitespace checks passed. No data/API changes, new images or change to completion estimates.

## 2026-09-03 — Reference-led premium menu checkpoint

- Implemented the supplied concept in the existing Blade application: image-left horizontal mobile cards, large square media, adjacent title/price/copy, reserved crop dimensions, minimal state chips, featured/new ribbons and readable sold-out states. FA retains `تومان`; EN/AR use `T`.
- Added a scoped `resources/css/menu.css` using existing colors/font conventions plus explicit menu tokens, compact glass header/search/category controls, responsive 2/3/4-column desktop/tablet grids and an accessible mobile navigation dock using existing routes. No ordering or non-functional plus button.
- Improved category scrollspy for tall/short sections and search-hidden categories; preserved the existing analytics requests and public data flow. Added two pure frontend regression tests.
- Used self-hosted Vazirmatn (SIL OFL) and six vendored Feather 4.29.2 icons (MIT license included); no package dependency was added. Font source: https://github.com/rastikerdar/vazirmatn/tree/v33.003. Icon source: https://github.com/feathericons/feather/tree/v4.29.2.
- Preserved and checkpointed the previously authorized local demo work: 24 products / 8 categories / 4 pages, independent FA/EN/AR copy, 8 local demo WebPs, lifecycle/fallback cases, idempotent provisioning and ownership-manifest guarded reset/removal. `php artisan denardi:provision-development --reset` rebuilds only owned demo records; `--remove` preserves unrelated/shared content and leaves source assets. Both are local-only. No new images were generated during this reference implementation.
- Included pre-existing public-page presentation and targeted Admin styling, the unprefixed Persian page binding fix and home featured-product read model without discarding the in-progress work.
- Visual QA: reference and rendered page compared together, then refined for category placement, type scale and combined featured/new state. Final screenshots and limitations are recorded in root `design-qa.md`. Browser checks covered three languages × eight widths, plus search, empty, navigation, scrollspy and all product states.
- Quality gate: **109 PHPUnit tests / 735 assertions**; final focused rerun **12 / 139**; PHPStan zero errors; Pint; **10 frontend tests**; Vite production build; Composer strict validation/security audit and npm audit — passed. Composer strict validation is now also explicit in CI. No new Lighthouse, screen-reader, physical-device or production validation claim.
- Commit **`2f59efb`** pushed to `develop/denardi-v1`. GitHub CI **[33732532543](https://github.com/lamatech-dev/LamaFood/actions/runs/33732532543)** passed every step, including bootstrap, clean migrations on disposable MySQL 8.4, PHP/frontend tests, static analysis, formatting, build and dependency audits. Overall completion estimates remain **95% V1 / 66% Go-live**; final content, real devices/UAT and infrastructure remain external dependencies.

## 2026-09-03 — Local menu-card refinement (working tree)

Superseded visually by the reference-led implementation below; retained as intermediate evidence.

- Preserved the existing dark/cyan identity and rectangular cards. On mobile, enlarged square media sits beside the name/price, with full-width descriptions, allergen notices and wrapping status chips underneath.
- Added refined borders/depth, larger type, logical RTL spacing and touch-safe hover behavior. Sold-out cards keep readable contrast; status text and all existing flags remain present.
- Kept Persian currency label `تومان`; English and Arabic use `T`. Price conversion/storage and data model are unchanged.
- Browser geometry checks passed for FA/EN/AR at 320, 375, 390, 430, 768, 1024 and 1440px: square media, no page or card-content horizontal overflow. At 390px, media increased from about 117px to 146px. Visually reviewed FA/EN/AR mobile and EN desktop, including branded no-image cards; checked Persian/English search and semantic headings/alt text. No recent browser errors/warnings were reported.
- Local verification: focused public/provisioning PHPUnit suite **12 tests / 139 assertions**, frontend **8 tests**, Pint and Vite production build passed. Font URL is intentionally resolved at runtime; Vite emits an informational warning. No new Lighthouse, physical-device, screen-reader or broken-network-image fault-injection result is claimed.
- Screenshots: ignored local `output/design-audit/after/cards-{fa,ar,en}-390.png` and `cards-en-1440.png`.
- This is a scoped local refinement within the pre-existing, uncommitted presentation work, not a new GitHub/CI checkpoint. Previous changes are preserved. Overall estimates and external blockers remain unchanged; broader phase verification/commit/CI is still pending.

## 2026-09-02 — Business User Management checkpoint

**Commit:** `2d7a465` — `feat: complete business user management`

- Added the real `Settings → Users & Access` workflow for Business-scoped list, create/invite, profile editing, allowed role assignment, active/inactive status, safe deactivation and managed Password Reset.
- Reused the existing Business Owner/Content Editor RBAC model; normal admins never receive a raw permission editor and cannot assign Lamatech privileges.
- Added persistent `is_active` state, inactive login/session enforcement, token/database-session revocation after access changes, and final-active-owner/self-lockout protection.
- Excluded Godfather and any protected Lamatech-level identity from normal user queries, counts and operations while preserving Godfather's instance-level capability contract.
- Recorded create, setup-link, update, deactivate and managed-reset events through the existing secret-redacting audit recorder.
- Confirmed the existing Denardi V1 model is Business-scoped; no unnecessary per-Branch user relation was introduced for the current single-Branch delivery.

Verification:

- Explicit clean `lamafood_test` MySQL migration from zero: passed; the additive migration was also applied to the local development database.
- PHPUnit: 107 tests / 708 assertions passed, including authorization, cross-Business isolation, protected identities, escalation, session revocation, lifecycle and audit cases.
- PHPStan/Larastan: zero errors; Pint and `git diff --check` passed.
- Frontend tests: 8 passed; Vite production build passed.
- Composer validation/audit and npm audit passed with zero known vulnerabilities.
- Playwright: authenticated Users & Access page and create dialog passed semantic/console checks; 375×812 mobile layout was visually verified with no browser error or warning.

GitHub Actions Run `33664462499`: **passed** on disposable MySQL 8.4 with clean migrations, PHPUnit, PHPStan, Pint, frontend tests/build and dependency audits.

No Staging, Production, real Denardi content, physical-device UAT, restore drill or Future/Out-of-V1 module was started.

## 2026-09-02 — Guarded Safe Restore foundation

**Commit:** `22ab386` — `feat: add guarded backup restore workflow`

- Added reference CLI Restore with non-mutating preflight by default and explicit `--execute` mode.
- Enforced completed/verified target and newer `pre_release` safety Backup, current instance/core manifest matching, checksum revalidation, maintenance mode, exact instance-bound confirmation phrase and a single-operation lock.
- Added streaming DB/full-archive staging, embedded-manifest validation, traversal/symlink rejection, configurable uncompressed-size ceiling and controlled uploads replacement.
- Kept database credentials out of command arguments/logs, preserved secret-exclusion recovery policy and added start/completed/failed audit plus protected operational logging.
- Production execution is disabled by default and additionally requires encrypted external target/safety artifacts; API/UI Restore remains inactive until its Godfather-only re-authentication/idempotency guards are implemented.
- Updated the deployment/recovery runbook. This checkpoint does not claim or execute a Restore Drill, Staging or Production Restore.

Verification:

- Clean `lamafood_test` migrations from zero: passed.
- PHPUnit: 87 tests, 464 assertions, passed; focused Backup/Restore: 9 tests, 34 assertions.
- PHPStan/Larastan: zero errors; Pint passed.
- Frontend tests: 8 passed; Vite production build passed.
- npm audit: zero known vulnerabilities. Local Composer CLI was unavailable; Composer validation/audit remains required in GitHub CI.

GitHub Actions Run `33633503081`: **passed** on disposable MySQL 8.4, including Composer install/validation/audit, application bootstrap, migrations from zero, 87 PHPUnit tests / 464 assertions, PHPStan, Pint, frontend tests/build and npm audit.

## 2026-09-02 — Persian default URL normalization

**Commit:** `6ccc4e1` — `fix: make Persian the unprefixed default locale`

- Persian public URLs are canonical without a locale prefix: `/`, `/menu`, `/about`, `/contact` and `/privacy`.
- English and Arabic retain explicit `/en/...` and `/ar/...` prefixes.
- Legacy `/fa` URLs permanently redirect to their unprefixed Persian equivalents with query parameters preserved.
- Internal navigation, language switching, QR destinations, JSON-LD, canonical/hreflang/x-default, sitemap, Admin brand links and the public PWA start URL follow the same contract.
- GitHub Actions Run `33630595154` passed on disposable MySQL 8.4 with migrations from zero, 81 PHPUnit tests / 441 assertions, PHPStan, Pint, frontend tests/build and dependency audits.

## 2026-09-02 — Final responsive UI/UX and release polish

**Commit:** `12659c9` — `feat: polish Denardi V1 responsive UI`

Completed:

- Refined Home, About, Contact, Privacy and Menu presentation while preserving CMS-driven content and all backend contracts.
- Shortened the public hero and moved useful discovery/menu content earlier on mobile.
- Added reusable product cards with real media or the existing Denardi asset fallback, explicit availability and feature states.
- Added sticky active category navigation, accessible search result/empty states, responsive footer and keyboard-safe mobile navigation.
- Refined Admin with a collapsible mobile navigation, responsive forms/cards, retryable loading state and accessible live status feedback.
- Preserved configuration-driven FA/EN/AR direction, routing, CMS content, SEO metadata and public language switching.

Verification:

- Clean explicit `lamafood_test` MySQL migration from zero: passed. The local development database was restored with the existing environment-based Godfather bootstrap and local-only idempotent Denardi provisioner after an environment-selection mistake; no Production data or secrets were involved.
- PHPUnit: 81 tests, 433 assertions, passed after the clean migration.
- PHPStan/Larastan: zero errors; Pint passed.
- Frontend tests: 8 passed, including localized public menu search helpers; Vite production build passed.
- Composer validation plus Composer/npm audits: passed with zero known vulnerabilities.
- Browser QA: 320/375/390/430/768/1440 public and Admin views, FA/EN/AR direction/content, single-H1 content pages and menu search/empty states passed without horizontal overflow.
- Physical iOS/Android devices, automated axe/screen-reader checks and Production-like Lighthouse were not claimed or performed.

GitHub CI:

- Run `33610458788`: **passed** on the disposable MySQL 8.4 service, including clean migrations, PHPUnit, PHPStan, Pint, frontend tests/build and dependency audits.

No content import, Staging, Production, restore drill, UAT or future modules were started.

## 2026-09-02 — Current Phase readiness gaps closed

**Commit:** `8519229` — `feat: close Denardi V1 readiness gaps`

Completed:

- Secure Password Reset for normal Business/Admin users with generic account-discovery-safe responses, 30-minute expiry, rate limiting, strong password validation, token/session revocation and audit; Godfather remains excluded and protected.
- Privacy-preserving Category View and Product View emission with published-subject validation, active Branch context, bot exclusion and 30-minute visitor deduplication; Menu View and QR Scan regressions remain covered.
- Application-side health visibility for application, database, storage read/write/delete, queue connectivity/backlog, persistent scheduler heartbeat and backup freshness.
- Locale-aware `CafeOrCoffeeShop`/`LocalBusiness` JSON-LD using published Contact/Location CMS data with optional verified configuration fallback; no fake Production business details are embedded.
- PWA production asset structure with 192/512 icons, a dedicated maskable icon, Apple touch icon, manifest validation and explicit API cache bypass; Admin data editing remains online-only with no offline mutation/synchronization.

Verification:

- Clean `lamafood_test` migration from zero: passed.
- PHPUnit: 81 tests, 433 assertions, passed.
- PHPStan/Larastan: passed with zero errors.
- Pint: passed.
- Frontend tests: 6 passed; Vite production build passed.
- Composer validation and Composer/npm security audits: passed with zero known vulnerabilities.
- Browser smoke: FA/EN/AR Home, Persian Menu, Admin login and Password Reset rendered with expected localized titles and no application console errors.
- Secret check: `.env` remains ignored/untracked; tracked examples contain placeholders or empty verified-data fields only.

GitHub CI:

- Run `33605426265`: **passed** for `8519229` on disposable MySQL 8.4.11, including dependency installs, application bootstrap, migrations from zero, all PHP/frontend tests, PHPStan, Pint, production build and dependency audits.

Remaining within this authorized Current Phase: none. The next recommended phase is targeted Release QA and mobile/Admin error-state polish plus the safe restore workflow. No Staging, Production, content import, UI redesign or future module work was started.

## 2026-09-02 — Admin/CMS/Menu/Media completion

**Commit:** `63cbb4f` — `feat: complete Denardi admin CMS menu and media`

Completed:

- CMS Page/Block management, preview, readiness, publishing and audit flows
- Product/Category management with branch-specific price and availability
- Media derivatives, localized metadata, references and deletion protection
- Structured Admin navigation and realistic local-only `fa/en/ar` demo content
- Public Product images and published CMS Media snapshots

Verification:

- Local MySQL migration and idempotent Denardi provisioning: passed
- PHPUnit: 65 tests, 315 assertions, passed
- PHPStan/Larastan: passed with zero errors
- Pint: passed
- Frontend tests: 6 passed
- Vite production build: passed
- Composer/npm audits: zero known vulnerabilities
- Browser smoke test: Persian and Arabic menus plus Admin login shell passed without application console errors
- Secret scan: `.env` ignored/untracked; no known credential committed

GitHub CI:

- Run `33553026988`: failed in `Run PHP/Laravel tests`; detailed public log was unavailable through the unauthenticated API.
- Run `33567196552`: the new annotation exposed a clean-checkout-only `ViteException` while rendering the authenticated CMS preview test before the frontend build step.
- Root cause: the local ignored Vite manifest masked a missing `withoutVite()` test isolation call; application behavior was not dependent on this local artifact.
- Fix commit `047830a`: the CMS preview feature test now disables Vite integration explicitly, matching the repository's other Blade view tests.
- Run `33567423994`: **passed** on the disposable MySQL 8.4.11 service, including migrations from zero, all PHP tests, PHPStan, Pint, frontend tests/build and dependency audits.

Remaining within this checkpoint: none. The Admin/CMS/Menu/Media completion pass is closed and must not expand into the deferred scopes without a new authorization.

## 2026-09-02 — Release QA security and analytics checkpoint

**Commit:** `b8ecab5` — `fix: harden Denardi V1 release QA`

Completed:

- Replaced browser-stored Sanctum bearer tokens with stateful Session/Sanctum authentication, CSRF cookie/header protection, login session rotation and logout invalidation.
- Protected the Admin dashboard at the server route and retained API authorization boundaries.
- Added baseline CSP framing/object/form restrictions, `nosniff`, Referrer-Policy, Permissions-Policy, frame denial and HTTPS Production-only HSTS.
- Completed business-scoped 30-day device and Table QR analytics breakdowns in the Admin API/UI.
- Expanded the Release security matrix across XSS escaping, IDOR for CMS/Menu/Media/QR, route permission coverage, Godfather isolation, session/CSRF behavior, tracked-secret scanning and malicious upload rejection.
- Added safe image decode validation so disguised executable content returns a validation error before storage instead of a server exception.

Verification:

- Clean `lamafood_test` migration from zero: passed.
- PHPUnit: 97 tests / 625 assertions, passed.
- PHPStan/Larastan: zero errors; Pint passed.
- Frontend tests: 8 passed; Vite production build passed.
- Composer validation and Composer/npm audits: passed with zero known vulnerabilities.
- Playwright local matrix: FA/EN/AR Home/Menu and Admin auth pages returned expected locale/direction, single H1 and no horizontal overflow; CSRF header, HttpOnly Session cookie, no localStorage token and unauthenticated Admin redirect were verified.
- Local Lighthouse only (not Staging/Production): FA/EN/AR Home and Persian Menu performance 100/99/100/98, accessibility 100, best-practices 100 and SEO 92/92/92/100. Home SEO remains content-dependent because final localized descriptions have not been supplied.
- `.env` remains ignored/untracked; no tracked private-key, real Godfather/database password or APP_KEY pattern was found.

GitHub CI:

- Run `33661676730`: **passed** on disposable MySQL 8.4 with clean migrations, PHPUnit, PHPStan, Pint, frontend tests/build and dependency audits.

No Staging, Production, real Denardi data, physical-device QA, screen-reader claim, restore drill or Future/Out-of-V1 module was started.

## Earlier checkpoints

- `foundation-v1-checkpoint`: Foundation architecture, localization, RBAC/Godfather and CI baseline.
- `3bf46e3`: localized CMS and Media Admin foundation.
- `bc8c23a`: V1 Menu/Table QR and analytics foundation.
- `2a13cbc`: three-language SEO/PWA shell.
- `cd48495`: secret-safe backup lifecycle.
- `7d73f70`: runnable local Denardi instance and development provisioning.
- `2b603e8`: real QR artwork downloads in SVG/PNG/PDF.

## 2026-09-02 — Delivery tracker introduced

- Added a single status tracker covering subsystems, `DEN-01` through `DEN-12`, release gates, external inputs and explicitly deferred work.
- Baseline estimate recorded as 82% implementation and 55% Go-live readiness.
- Future checkpoint rule: update the tracker, implementation log and Git commit together.

## 2026-09-02 — Persistent tracker and visual dashboard expanded

- Expanded the canonical tracker to 159 evidence-backed control items across all delivery, operations, content and explicitly deferred areas.
- Added a self-contained responsive HTML dashboard with matching counts, status filters, readiness metrics, roadmap, blocker groups and Git/CI evidence.
- Confirmed this checkpoint is project tracking only and changes no application behavior.
- Checkpoint commit `259a348` passed GitHub Actions run `33569432914` on the disposable MySQL 8.4.11 service.

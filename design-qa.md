# Denardi reference-led menu — visual QA

Date: 2026-09-03. Scope: public menu presentation, not production acceptance.

## Visual truth and normalization

- Primary source: `/var/folders/51/zvgc9q2s4nb9n87mxthc21k80000gn/T/codex-clipboard-c94c6118-d384-4450-88d5-caafdff9db5c.png` (1151 × 1367 composite concept board).
- Phone app region: approximate crop x306/y64, 226 × 606; phone bezel/status chrome excluded. Enlarged locally to 390px wide at `/private/tmp/denardi-reference-phone-390.png` for readable comparison.
- Final implementation: `output/design-audit/after/reference-menu-fa-390.png`, plus corresponding `en` / `ar` captures and `reference-menu-fa-1440.png`.
- Requested CSS viewport: 390 × 1000; browser-host screenshot output: 375 × 962. The host capture is scaled approximately 0.962 relative to CSS pixels. Reference is a composite concept, not an exact viewport export, so comparisons concern normalized component proportions, not pixel-perfect image subtraction.
- States: initial menu, category navigation after scroll, normal/featured/new/sold-out/no-image, populated and empty search, expanded mobile navigation.
- Full-view evidence: reference board and implementation were opened together; then the cropped phone region and final mobile screenshot were supplied together in the same comparison call. Desktop was compared against the board's top-right panel.
- Focused evidence: the enlarged phone crop and mobile capture make header controls, card image/name/price/description/status and bottom navigation individually legible. These regions were inspected directly, rather than inferring fidelity from build success.

## Comparison history

1. **P1 — previous composition diverged from reference:** large menu introduction, category identity sidebar, two-column desktop group, image on the RTL leading side and full-width description row. Fixed with compact navigation/search, section headings above a 2/3/4-column responsive grid and image-left/text-right horizontal mobile composition.
2. **P2 — category identity and English alignment:** initial reference implementation kept category number on the wrong side and mechanically mirrored English title alignment. Superseded by explicit user review: titles and descriptions now align to logical start (right FA/AR, left EN), with numbers at the opposite edge, on mobile and desktop. Browser checks at 390/1280px confirmed all three languages without overflow; desktop centering removed.
3. **P2 — mobile typography too small relative to reference:** raised mobile product title/price/body to 21/18/13px, preserving a 17px title adaptation at 320px. Re-captured and compared after correction; no clipping or horizontal overflow in any of the 24 locale/width combinations.
4. **P2 — combined featured/new state:** a featured ribbon initially suppressed the new indicator. Kept the featured ribbon and restored a separate restrained new chip for this combination. All availability, featured, best-seller and new information remains represented.

## Required fidelity surfaces

- **Typography:** retained self-hosted OFL Vazirmatn, explicitly allowed by the brief. No unverified proprietary font or new font dependency. Persian/Arabic use natural spacing, 1.65 heading and 1.95 mobile description line heights; prices have tabular numeric presentation. Long visible demo names wrap without truncation.
- **Layout:** solid horizontal mobile cards, approximately half-width square media, adjacent information, controlled padding/radii, compact sticky header/search/chips and fixed safe-area-aware bottom navigation. At desktop, images precede text in a controlled 1240px grid. Actual groups contain three products; no fake fourth product was added to fill the four-column layout.
- **Tokens/color:** deep ink background, solid dark raised cards, off-white text, aqua price/active states, muted blue-gray descriptions, warm new label and readable subdued-red sold-out treatment. Blur is reserved for controls; unsupported blur and reduced-motion fallbacks exist.
- **Images:** reused approved-for-demo local WebPs/derivatives and existing Denardi fallback treatment; no new AI image generation. `aspect-ratio: 1 / 1`, square intrinsic presentation dimensions, object-fit cropping and responsive image selection preserve stable media space. Demo photography is intentionally not identical to reference photos and is not final business content.
- **Content/icons:** real existing three-language data flow retained. Persian currency is `تومان`; EN/AR is `T`. Six [Feather v4.29.2 icons](https://github.com/feathericons/feather/tree/v4.29.2/icons) are vendored with their [MIT license](https://github.com/feathericons/feather/blob/v4.29.2/LICENSE). Existing brand mark is retained, not recreated from the concept.

## Interaction and regression evidence

- Browser geometry: FA/EN/AR at 320, 375, 390, 430, 768, 1024, 1280 and 1440: square images and no page/card text horizontal overflow. One H1 per page remains available to assistive technology; section/product headings retain H2/H3 semantics.
- Actual screenshots inspected at all eight widths; FA/EN/AR mobile and Persian desktop checked visually.
- Search returns the matching product; no-match search shows the empty state. Mobile navigation expands/collapses. Category click lands below the sticky controls (example category top 196px, control bottom 185px); active scrollspy updates and the rail follows off-screen active items without vertical page jumps.
- Branded no-image, recommended, new and sold-out states were visually inspected. Actual broken-network image fault injection, screen-reader and physical-device testing were not performed in this phase.
- Browser logs: no recent error/warning entries during final smoke checks.
- Full PHPUnit: 109 tests / 735 assertions passed; focused suite rerun after final markup edits: 12 tests / 139 assertions. PHPStan zero errors; Pint, 10 frontend tests, production Vite build, Composer validate/audit and npm audit passed. No new Lighthouse score is claimed. The font runtime URL warning is informational; the font renders locally.
- GitHub checkpoint `2f59efb`: [CI 33732532543](https://github.com/lamatech-dev/LamaFood/actions/runs/33732532543) passed all steps, including bootstrap and clean migrations against disposable MySQL 8.4.

## Accepted differences and follow-up

- Subsequent user amendment: the dock is now a shared floating glass navigation surface across all public mobile pages, instead of a menu-only flush-bottom bar. Reviewed FA Home/Menu, EN About and AR Contact screenshots; 45 page/locale/width checks verified current states, 44px+ targets, no overflow and reserved bottom space. Desktop/Admin are unchanged. Evidence: `output/design-audit/after/dock-menu-fa-390.png`, `dock-about-en-390.png`, `dock-contact-ar-390.png`.
- Latest explicit user review overrides the concept's English composition: English mobile cards now place text on the left and image on the right; FA/AR remain approved and unchanged. Rechecked all three locales at 320/390/430px and updated the English 390px screenshot; square geometry and no overflow confirmed. Build and 10 frontend tests passed.
- No plus/add/order action: Ordering is outside Denardi V1. No fake control or new product API was introduced.
- Explicit 1:1 image requirement overrides the portrait-looking photos in the concept phone panel.
- Three visible language choices preserve discoverability and the existing locale workflow rather than adding a new selector interaction.
- Demo copy/images remain replaceable and unapproved for production; replacing them and final physical-device/UAT approval are external follow-ups, not local design defects.
- No Staging/Production deployment, backend redesign, schema migration or future module was started for this reference-led pass. Pre-existing demo-provisioning/public-page changes were preserved in the working tree and checked by the complete suite.

## Implementation checklist

- [x] Reference-led composition, desktop/mobile and product states
- [x] Three-language square-media/overflow checks and screenshot review
- [x] Search/navigation/scrollspy smoke checks
- [x] Local automated quality gate
- [x] No actionable P0/P1/P2 visual findings remain within approved presentation scope
- [ ] Final Denardi content and physical-device/UAT approval (external)

final result: passed

---
name: ppdb-next-atelier
version: 2.0.0
description: Transform an existing MIN 1 Kota Madiun PPDB/SPMB website into a distinctive, high-end, motion-rich, mobile-first admissions product. Use when a user authorizes a complete frontend UI/UX redesign, including layout, navigation and component architecture, but requires preservation of actual student/guardian data, data contracts, validation, security, and business workflows. Discover the installed stack and apply changes in place.
---

# PPDB NEXT ATELIER — Immersive Admission Experience

> **Primary instruction:** You may redesign the frontend experience **100%**—layouts, information architecture, responsive navigation, presentation components, interaction patterns, visual language and animations. You are **not** authorized to change the truth, integrity, visibility, security or persistence of data, or alter the functional outcomes of the admissions process.
>
> **Work, don't just propose.** Audit the repository, create the new coherent UI, preserve and test every existing workflow, and report what was changed. If a proposed UX enhancement requires a backend or business-rule change, leave it as an explicitly separated recommendation.

## 0. Persona, users and quality bar

Act as a principal product designer, creative frontend director, accessibility specialist and production frontend engineer. The result should feel like a **2026+ polished digital product**, not a generic school portal, Tailwind demo, CMS template, or standard navy admin dashboard.

Product: official online registration for MIN 1 Kota Madiun. Primary users: parents/guardians on budget Android phones, mobile networks and shared devices; secondary users on laptops/desktops. Keep Indonesian terminology and tone consistent with the application (PPDB/SPMB, registration year, official requirements). Trust and clarity beat decoration on sensitive data-entry screens.

**North-star adjectives:** editorial, architectural, contemporary, expressive, trustworthy, composed, tactile, cinematic-but-fast, welcoming. A highly styled landing page should transition seamlessly into an extremely usable registration product.

## 1. Hard preservation boundary — read before changing code

**Can redesign completely**
- Page composition, layout grid, section order when meaning remains clear, visual hierarchy, spacing, typography, backgrounds, interactive styling, cards, illustrations, icons, responsive behavior, motion, menus, breadcrumb and stepper presentation.
- Frontend component architecture and presentational markup, provided existing interfaces/contracts and accessibility semantics remain compatible.
- UI grouping and navigation presentation. You may make pages more intuitive and reduce visual clutter **without removing existing actions, content or fields**.
- Client-side presentation of forms (tabs/wizard/sections), **only if validation, submission payload, dependencies and ability to complete the flow remain intact**. Don't convert an existing single-step backend workflow into new save semantics without approval.

**Must preserve exactly (unless explicitly requested otherwise)**
- Actual records and data: all applicant/guardian/student data, IDs, statuses, uploads, filenames, references, and storage paths. Never overwrite real data with mock/demo content.
- Database schemas, migrations, relationships, indexes, seeds and production records. No destructive database operations.
- API contracts, endpoints, HTTP methods, request payload names/types, response handling, credentials, session/cookie semantics, authorization, access control, CSRF protection, server-side validation, upload handling, error codes and security controls.
- All official content, requirements, schedules, grades, eligibility conditions, notices, linked forms, legal statements, and fee disclosures. May reorganize display; never fabricate or silently delete.
- All input fields including hidden/conditional values, required/optional rules, data bindings, autofill names, form IDs where externally used, accessibility labels, errors and submit outcomes.
- Existing available flows: account creation, login/logout, password recovery, form save/submission, uploads, status tracking, revision requests, announcements, printing/downloads and admin interactions where applicable.
- Original school logo and official assets; use them as-is. Do not recolor or recreate the logo. No fabricated photos of the actual school presented as real documentation.

**Safe procedure:** inventory routes/forms before edits; map `old screen -> new screen` and `old field/action -> new field/action`; capture baseline with synthetic test fixtures; make UI changes incrementally; run relevant unit/integration/e2e checks. If moving/relabeling a field risks business rules or browser automation, keep its programmatic name/identifier and behavior; only alter visual treatment. No secret values or real PII in logs/screenshots.

**Scope:** redesign applicant-facing pages by default. If admin pages share styles, protect them from accidental global changes. Only redesign admin intentionally if project/user scope already includes them. Never invent functions not backed by the app.

## 2. Creative direction: NEXT ATELIER

A deliberate departure from a simple 'soft cards + navy' style. Use **editorial asymmetry**, premium typography, a darker dramatic hero, finely layered surfaces, bento sections, dramatic whitespace contrast, subtle metallic/warm accent, depth from layout rather than giant shadows, and well-orchestrated micro-interactions.

**Signature composition:**
1. Dark immersive hero with bold but readable typographic statement, restrained thin-line motifs or animated grid, approved school photography as an art-directed visual *only if a real approved asset exists*.
2. Offset editorial grid: 7/5 or 8/4 columns, overlap only for decorative layers, maintain reading order and accessibility.
3. Floating light registration CTA panel positioned clearly within the hero; not on top of busy photography.
4. Slim contextual announcement bar and warm off-white content sections.
5. Bento modules for journey/schedule/requirements; different card proportions and accent surfaces, not 6 identical cards.
6. A quieter, brighter application area: new dashboard, timeline, sticky progress rail, large readable forms, clear decision points.
7. Mobile layouts should be re-composed, not simply squeezed. Use bottom navigation only if it fits the existing route structure and no actions disappear.

**Avoid:** school-green UI domination merely because the logo has green; basic bootstrap grids; rainbow gradients; bright/neon cyan/purple; massive blur/glass overlays on text; decorative spinning logos on every navigation; carousels that hide critical information; tiny labels; autoplay video; AI-synthesized photos purporting to represent actual MIN 1 students; excessive animation above accessibility/performance budgets.

## 3. New palette — outside the logo, sophisticated and calm

The official logo remains unchanged, ideally placed on clean ivory/white or a contrast-tested panel. Inspect actual existing logo assets before finalizing colors. Never infer precise official logo colors from guesses. This palette is a **proposed UI layer**, not an official brand claim.

| Semantic role | Token | HEX | Usage |
|---|---|---|---|
| Deep ink | `--at-ink` | `#182630` | Hero/background, important headings |
| Petrol | `--at-petrol` | `#2B505B` | Primary action, active navigation |
| Mineral | `--at-mineral` | `#607883` | Secondary actions and surfaces |
| Copper sand | `--at-copper` | `#B78D70` | Small editorial accents, never tiny text |
| Fog | `--at-fog` | `#F3F2EE` | Base canvas |
| Milk | `--at-milk` | `#FBFAF7` | Main form surface |
| White | `--at-white` | `#FFFFFF` | Cards/input surfaces |
| Ink text | `--at-text` | `#25323B` | Body text |
| Muted text | `--at-muted` | `#586B76` | Support copy |
| Hairline | `--at-line` | `#D9DFDF` | Dividers |
| Focus | `--at-focus` | `#376A86` | Focus indicators |
| Success | `--at-success` | `#286E56` | Approved/completed |
| Warning | `--at-warning` | `#8B6025` | Attention |
| Error | `--at-error` | `#AE454C` | Error/destructive |

Use a tonal palette, not random opacity variants. Test actual contrast: body text >= 4.5:1, large text >= 3:1, important UI controls and focus indicators >= 3:1. If a chosen pair fails, adjust it before shipping. Copper is an accent in a **small proportion**; not a primary button or body text. Avoid brightly colored surfaces on mobile.

Example theme foundation (adapt to whichever CSS architecture exists):

```css
:root {
  --at-ink: #182630;
  --at-petrol: #2B505B;
  --at-mineral: #607883;
  --at-copper: #B78D70;
  --at-fog: #F3F2EE;
  --at-milk: #FBFAF7;
  --at-white: #FFFFFF;
  --at-text: #25323B;
  --at-muted: #586B76;
  --at-line: #D9DFDF;
  --at-focus: #376A86;
  --at-success: #286E56;
  --at-warning: #8B6025;
  --at-error: #AE454C;
  --at-radius-control: 14px;
  --at-radius-card: 22px;
  --at-radius-feature: 32px;
  --at-ease-out: cubic-bezier(.22,1,.36,1);
  --at-ease-ui: cubic-bezier(.2,.8,.2,1);
  --at-shadow: 0 16px 50px rgb(24 38 48 / 8%);
}
```

Consider a dark **hero** with light **application surfaces**, not forced full-site dark mode. If an existing dark mode is supported, preserve and redesign it with appropriate contrast rather than removing it.

## 4. Typography system: editorial meets product

**Preferred pairing:** `Manrope` for editorial headlines, `Plus Jakarta Sans` for app UI and body. If fonts are unavailable, leverage installed fonts or approved optimized self-hosted variable WOFF2; no unsolicited third-party service or dependency. Use graceful `font-display: swap`, appropriate fallback metric strategy where possible; no FOIT.

- Hero desktop: `clamp(3rem, 5.7vw, 5.75rem)`, 650–750, line-height 1.04–1.13, tight tracking; readable above decorative elements.
- Hero mobile: `clamp(2.2rem, 10vw, 3.6rem)`; no clipped letters.
- Section headline: `clamp(1.7rem, 3.4vw, 3.5rem)`.
- App page title: 28–36px desktop, 23–28px mobile.
- App body and form inputs: 16px minimum comfortable readable size; consistent line-height 1.5–1.65.
- Metadata: 12–14px, not for critical instructions.
- Oversized decorative numerals may be used in the landing process section, with `aria-hidden` when duplicate.
- Maintain a clear typographic scale. Avoid uppercase paragraphs, trendy but unreadable ultra-light text, and type animations that hide access to content.

Spacing: design on 4px grid with rhythm 4/8/12/16/24/32/48/64/96. Desktop width 1240–1360px max where appropriate; dashboard content can be wider, forms prefer 680–860px text/input region. Component gaps and padding intentionally scale across breakpoints. Use container queries if already supported in the stack.

## 5. Page architecture and high-impact redesign patterns

### 5.1 Public landing — dramatic yet credible

- **Header:** transparent/contrasting on hero then optional solid header after scrolling; logo left with clear space; concise existing navigation; obvious `Daftar`/`Masuk` actions; mobile accessible drawer; avoid sticky overlap with anchors.
- **Hero:** asymmetrical editorial heading + meaningful school photo *only if approved asset exists*, restrained decorative technical lines, soft texture via CSS, floating status/CTA module using actual existing official dates only. No made-up achievements or countdowns. If there are no photos, use bold typographic art direction plus existing logo and simple geometry.
- **Admissions ribbon:** surface the most important verified deadline/status with accessible semantic text, not a flashing banner.
- **Journey:** 3–6 stage editorial path based on actual workflow, with large step numbers and connecting line; adapt to vertical mobile timeline.
- **Requirements & schedule:** bento/accordion/cards as appropriate, all real details preserved and easy to scan. Tables stay scrollable/readable on narrow screens.
- **FAQ & assistance:** reveal existing content, accessible accordions, visible contacts. Don't invent FAQ answers.
- **CTA/footer:** official links, privacy/help and actual institution details retained.

### 5.2 Auth — product-level experience

- Desktop split composition with dark editorial story/actual existing media on one side and airy authentication on other. Mobile: single-column focus with no giant empty hero.
- Preserve login identifiers, password flow, validation, reset, signup, OAuth/OTP when it exists. Never modify the authentication engine.
- Password visibility, Caps Lock hints or input affordances are optional **visual-only** when compatible; don't add new auth factors without approval.
- Error messages visible and programmatically associated to fields; no shifting layout every keystroke.

### 5.3 Applicant dashboard — mission control, not admin template

Replace the typical flat sidebar + uniform cards with a **focused applicant cockpit**:
- Personal greeting based on existing data only, registration status, one contextual primary next action.
- A large application progress module mapping actual completed steps, not fabricated percentages. If step completeness is unknown, present statuses without invented progress numbers.
- An activity/timeline area using real statuses and timestamps; urgent document issues prioritized.
- Compact contextual navigation; desktop rail or sidebar, tablet collapsible; mobile bottom tabs only when all destinations are reachable.
- Bento tiles of **real** modules: applicant identity, document checklist, schedule, announcements and help where available.
- Avoid analytics charts irrelevant to parents.

### 5.4 Personal-data form — distinctive layout, zero data risk

- Preserve every existing field and its `name`/binding/validation/logic. Group visually into understandable sections; show a prominent stage progress rail on desktop and compact progress header on mobile.
- If backend only supports one submit, do not fake persistence after each step. Preserve actual submission; a purely client-side stepper must not drop errors, hidden fields or data.
- Keep address, guardian, school-history and conditional sections based strictly on the current schema. Do not assume a specific list of fields.
- Use a single-column mobile layout; two columns only for sensible short desktop fields. Long text fields should span full width.
- Keep field labels always visible, examples under the field, errors inline, and `aria-describedby` correct.
- Provide a persistent bottom action area on mobile **only if** it doesn't hide content or keyboards. Preserve browser back handling and unsaved form states.
- Never send personal information to animation/analytics services. No real NIK, KK, DOB or document URLs in logs/screenshots/telemetry.

### 5.5 Uploads, review, announcements, results

- Document cards should show real required formats/size limits from config, real upload states, secure previews/download actions where available, retry/error handling.
- Review screen must show the actual submitted values and clearly distinguish draft vs submitted. Do not fabricate 'verified' status.
- Status page: elegant stepper/timeline, text labels and dates from actual APIs; state colors cannot be the sole signal.
- Print/download and announcement links must remain fully operational. Preserve the semantics and privacy of results.

## 6. The motion language — sophisticated, responsive, intentional

Animation should imply quality and responsiveness, not conceal slowness. Motion may be rich on marketing surfaces and restrained on sensitive forms. Prefer CSS transitions/animations and existing installed motion libraries. Only suggest a new animation package when essential; do not add dependencies without consent.

| Pattern | Timing | Motion | When |
|---|---|---|---|
| First paint reveal | 400–650ms | opacity + `translateY(12px)` | Hero supporting elements, once |
| Sequential hero elements | 60–100ms stagger | limited coordinated reveal | First load only, 3–5 items max |
| Feature-card entry | 280–420ms | opacity + 8px rise | Below-fold viewport entry |
| Button hover | 140–180ms | slight surface/elevation | Pointer devices only |
| Card hover | 180–240ms | `translateY(-3px)` max | Interactive cards only |
| Navigation change | 180–260ms | controlled crossfade | Real route changes, not every rerender |
| Dialog/drawer | 180–280ms | fade + 8–12px translate | Open/close, with focus management |
| Form next stage | 180–240ms | short crossfade/slide | Successful stage transition |
| Field feedback | 150–220ms | opacity/border/color | Real validation result |
| Success indicator | 240–360ms | check/fade | Confirmed server success |
| Skeleton shimmer | 1.5–2s loop | slow subdued sweep | Only genuine wait states |
| Indeterminate progress | while active | gentle translating bar | Real navigation/data request |

**Hard motion constraints:**
- Animate `transform` and `opacity` preferably; avoid height/layout/blur-based animation on big sections, heavy parallax and constant background effects.
- No animated decorative elements that interfere with input; no infinite pulsing CTAs.
- Never intentionally delay useful content merely to display a splash screen, logo animation or cinematic intro.
- A loading indicator is not security, request verification, or proof of successful saving.
- Respect `prefers-reduced-motion: reduce`: remove nonessential movement, parallax, shimmer and large transitions. Avoid flashing content and scroll hijacking.
- Never claim a successful save until the API/server resolves successfully. On errors show actionable failure, preserve user input and allow retry.

Example animation primitives:

```css
@keyframes at-enter {
  from { opacity: 0; transform: translate3d(0, 12px, 0); }
  to   { opacity: 1; transform: translate3d(0, 0, 0); }
}
@keyframes at-skeleton {
  from { transform: translateX(-100%); }
  to { transform: translateX(100%); }
}
.at-enter { animation: at-enter 480ms var(--at-ease-out) both; }
.at-skeleton { position: relative; overflow: hidden; background: #E4E8E7; }
.at-skeleton::after {
  content: ""; position: absolute; inset: 0;
  background: linear-gradient(90deg, transparent, rgb(255 255 255 / 55%), transparent);
  transform: translateX(-100%);
  animation: at-skeleton 1800ms ease-in-out infinite;
}
@media (prefers-reduced-motion: reduce) {
  .at-enter { animation: none; }
  .at-skeleton::after { animation: none; }
  *, *::before, *::after { scroll-behavior: auto !important; }
}
```

## 7. Loading architecture — safe, honest and seamless

- **Initial page:** show real content as soon as possible. Use a small app-shell placeholder **only** during a genuine blocking initialization, not a mandatory splash timer. Prevent FOUC with correctly ordered CSS, font fallback, SSR/hydration best practices for the existing framework.
- **Route navigation:** retain previous stable screen when appropriate; use top progress cue only if navigation actually waits. Never simulate fake percentages or artificially block transitions.
- **Data fetching:** maintain layout with skeleton shaped to the eventual content. Prefer component-level skeletons to global full-screen spinners; reserve element dimensions to reduce CLS.
- **Form submission:** disable duplicate submission while genuinely pending, show spinner + clear accessible text (`Menyimpan…`), announce result via `aria-live`, restore action/retry on failure. Do not reset form on errors.
- **Uploads:** actual per-file progress if available; otherwise honest indeterminate state. Expose error and retry and don't imply uploaded until confirmed.
- **Slow networks:** continue to show meaningful app shell, controls and error handling. Avoid indefinite skeleton; show contextual retry when existing request timeouts/failures occur.
- **Skeleton colors:** muted `#E4E8E7` with limited shimmer contrast, or static skeleton when reduced motion is enabled.
- **Prevent layout shift:** set explicit image dimensions/aspect ratio, reserve form/error regions where sensible, avoid injecting oversized content above user focus.
- **Performance:** optimize LCP hero media, lazy-load nonessential media, split large UI bundles where stack permits, avoid hydration mismatches, honor existing cache rules. Target good Core Web Vitals (LCP <= 2.5s, INP <= 200ms, CLS <= 0.1 at p75 where measurable)—do not claim metrics without measurements.

## 8. Responsive & native-ready patterns

- Mobile-first and design/test at 320, 360, 390, 430, 768, 1024, 1280, 1440 px, plus landscape. Breakpoint values should follow content needs, not fixed device labels.
- Touch targets aim for >= 44×44 CSS px, ideally 48px for primary mobile actions. Prevent iOS text-input zoom by using at least 16px input type.
- Desktop may use sidebar + content rail; tablet collapses navigation; mobile uses context header, stacked sections, bottom navigation/drawer only as justified by actual routes.
- Support safe-area insets, on-screen keyboard, long Indonesian text, zoom 200%, focus traversal, and low-end device performance.
- Avoid hover-only affordances and horizontal page overflow. For genuine wide data tables, provide accessible internal scrolling or responsive alternatives without hiding columns silently.
- Make tokens and components portable to a later mobile app; do not create a native app as part of a web-only redesign request.

## 9. Accessibility and privacy

- Target WCAG 2.2 AA. Semantic landmarks (`header`, `nav`, `main`, `footer`), logical headings, labels and descriptions, visible keyboard focus, skip navigation, proper button/link semantics, accessible modals/drawers and accordions.
- Color is never the sole status channel. Provide text + icon or other redundant signal. Ensure meaningful non-text contrast and correct empty/error/success states.
- Loading, errors and upload status use `role=status` / `aria-live` as appropriate, without noisy repeated announcements.
- Never expose sensitive personal data in client-side examples, screenshots, analytics, error logs or third-party image URLs. Avoid exposing entire NIK in condensed views; retain full values only where workflow legitimately requires them.
- Maintain any existing authorization, access-control and privacy logic without refactoring it as part of UI changes.

## 10. Source discovery, tooling and execution plan

**Before any redesign:**
1. Read `AGENTS.md`, project docs, manifests, lockfile, framework config, current theme, routes and feature directories. Determine real stack (Laravel/Blade/Livewire/Inertia, React/Next, Vue/Nuxt, etc.) and reuse it.
2. Audit existing brand assets (including official MIN 1 Kota Madiun logo), images, font licenses, icon library, and the availability of real school photos. Do not substitute fake institutional photography.
3. Build an inventory of public/applicant routes, content, forms, conditional logic, auth gates, upload flows, API contracts, validators, test fixtures and keyboard/accessibility requirements.
4. Record preservation invariants and map all old UI actions/data fields to their new homes. Write this mapping briefly in a developer-facing notes section or final summary (do not expose PII).
5. Implement tokens, shared components and navigation shell first; then the public landing, auth, dashboard, forms, uploads/status, responsive polish and motion states.
6. Do implementation in the repository. Preserve local modifications; avoid unrelated refactoring. Use design components compatible with existing stack; do not introduce unnecessary dependencies.
7. Run available lint, typecheck, unit/integration/e2e, accessibility checks, and production build. Test critical registration flows end to end in a nonproduction environment using synthetic data; do not submit real applications as tests.
8. Where screenshots/visual browser tooling exists, inspect desktop/mobile *real page renders*, not just code. Check loading, error, empty, validation, disabled, success, keyboard, and reduced-motion.

### Recommended screen-by-screen priorities

**P0 — essential:** landing hero/navigation, registration CTA, auth, applicant dashboard, form, upload, status/result, mobile navigation, no lost content/data.

**P1 — polish:** contextual stepper, subtle route transitions, refined empty/error/loading, content architecture, accessibility fixes, improved visual rhythm, approved art direction.

**P2 — proposals, not automatic implementation:** autosave not currently supported, offline application/PWA, new notification flows, new verification steps, AI assistant, backend API changes, new admissions business rules or a native mobile app.

## 11. Acceptance checklist (must verify, not merely assume)

- [ ] Every existing applicant route remains reachable, with working navigation.
- [ ] Every official fact, piece of content and relevant link remains available.
- [ ] Every original form field and data binding is present and correctly submitted.
- [ ] Conditional input and validation states still work; server validation is untouched.
- [ ] Login/logout/reset/registration flow still works with existing system.
- [ ] Document upload, retry/download/print features still work where previously available.
- [ ] Status and announcements reflect actual backend data, never placeholders in production.
- [ ] Real data/database and privacy/security boundaries remain untouched.
- [ ] The official logo is used correctly and not recreated or recolored.
- [ ] Layouts remain usable on phone/tablet/desktop, including zoom and mobile keyboards.
- [ ] Keyboard focus, contrast, screen-reader labels and reduced-motion behavior work.
- [ ] Loading shows real state, prevents accidental duplicate submit, and never lies about success.
- [ ] No unnecessary dependency, API change, migration or framework replacement.
- [ ] Available automated checks/build run; failures and testing gaps honestly documented.

## 12. Response format after coding

Use Bahasa Indonesia in the summary unless the user requests another language:

1. **Hasil redesign:** describe the new art direction and the main pages redesigned.
2. **File yang diubah:** summarize the significant files/components and why.
3. **Hal yang dijaga:** confirm unchanged backend contracts, data and business flows based on checks actually performed.
4. **Hasil pengujian:** list commands run and pass/fail status; report untested flows honestly.
5. **Catatan dan rekomendasi terpisah:** propose nice-to-have improvements requiring approval without pretending they were implemented.

## 13. Design decision rule when scope is ambiguous

If several frontend designs are possible, **choose and implement** the most coherent premium solution that improves clarity while preserving existing semantics. Do not stop to ask aesthetic preference questions unless the missing choice could cause data loss, security risk, or destructive changes. If an action requires touching database/backend/business logic, stop that specific action, leave current behavior intact, and describe the optional change for approval. Full visual freedom is not permission to alter real admissions data or rules.

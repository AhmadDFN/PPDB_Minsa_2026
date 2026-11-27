---
name: calm-academic-ppdb
description: Redesign an existing MIN 1 Kota Madiun PPDB/SPMB applicant-facing web application into a calm, elegant, modern, mobile-first experience. Use for landing pages, authentication, applicant dashboards, multi-step forms, document upload, status pages, and accessible loading/motion states. Preserve existing content, business logic, routes, data contracts, and functionality.
---

# Calm Academic — PPDB MIN 1 Kota Madiun

## 1. Mission and scope

Act as a senior UI/UX designer and frontend engineer. Modernize the existing application, not rebuild its business processes. Deliver production-ready UI improvements that feel like a premium education portal rather than a generic admin template. Design for parents and guardians with varying digital literacy. All existing application content is the source of truth, including terminology (PPDB or SPMB), dates, requirements, branding, and Indonesian copy.

Default scope: applicant-facing public landing, authentication, dashboard, registration forms, documents, announcements, and account pages. Do not redesign administrator pages unless requested. Apply the same design tokens across desktop, tablet, and mobile so the design can later be ported to a native application.

### Non-negotiable preservation rules

- Preserve all existing visible content, labels, instructions, links, legal notices, images, and functional actions. Content may be visually regrouped without changing meaning or omitting information.
- Preserve URLs, routes, API endpoints, request/response schemas, database, authentication, permissions, validation, submission handlers, storage, and business rules.
- Keep all fields, required/optional rules, conditional fields, error messages, document limits, and registration states. Do not add or remove required fields or change the official process.
- Never fabricate dates, school statistics, testimonials, admission rules, or status information. Never replace the official logo with a generated logo. Use actual existing assets; if unavailable, report the gap.
- Do not introduce a new framework, dependency, paid service, external font request, tracking, or backend migration without a clear need and approval. Prefer existing stack and components. Do not overwrite unrelated changes or secrets.
- Do not turn a working control into a decorative mockup. Keep keyboard navigation, native form submission semantics, existing integrations, and error recovery working.
- Suggestions beyond visual scope are welcome, but list them separately as proposals; obtain approval before changing behavior or content. Examples: autosave, OTP, new onboarding steps, new notifications, and native app functionality.

## 2. Execution workflow

1. Read repository instructions (including AGENTS.md), package manifests, routes, layouts, theme configuration, CSS, component library, existing assets, and available tests. Identify frontend stack and styling conventions.
2. Inventory applicant-facing pages and states. Trace each action, form handler, validation rule, upload flow, and navigation destination before editing. Identify existing loading boundaries and asynchronous data sources. Record baseline screenshots where tooling permits, using synthetic applicant data only.
3. Establish a shared theme/token layer, then update shared primitives (buttons, inputs, cards, typography, feedback, navigation) and layouts. Avoid global CSS changes that unintentionally affect admin pages.
4. Redesign landing, auth, dashboard, forms, documents and status views incrementally. Maintain existing components and their interfaces where possible. Apply motion to meaningful transitions, not every element.
5. Inspect responsive layouts and real loading, empty, error, success, disabled, and reduced-motion states. Run existing lint, typecheck, tests and build commands where available. Add focused visual/interaction regression checks in the existing test stack when appropriate.
6. Report changed files, preservation checks, screenshots or test results, limitations, and optional UX improvements. Do not claim tests passed unless actually executed successfully. Keep changes reviewable and avoid unrelated refactors.

## 3. Visual direction

**Calm Academic:** quiet, warm, trustworthy, editorial education landing pages combined with clean product-style interfaces. Use restrained color, generous whitespace, a strong typographic hierarchy, subtle borders, and modest elevation. Avoid neon, heavy gradients, excessive glassmorphism, giant shadows, excessive pill shapes, noisy decorative patterns, or generic AI-generated marketing visuals.

Use the original MIN 1 Kota Madiun logo unchanged, on a neutral background with clear space. The palette intentionally uses navy, warm ivory, muted blue and bronze rather than repeating the logo colors. Inspect the actual logo asset in the repository; the palette below is a starting direction, not a sampled official brand palette. Adjust only if necessary for harmony and accessible contrast; report changes.

### Design tokens (light theme)

Use semantic names and map to the existing theme system. Example CSS variables (adapt to stack):

```css
:root {
  --ppdb-primary: #26384A;
  --ppdb-primary-hover: #1D2D3D;
  --ppdb-secondary: #536C82;
  --ppdb-accent: #B59A78;
  --ppdb-background: #F7F6F2;
  --ppdb-surface: #FFFFFF;
  --ppdb-surface-muted: #E9EDF1;
  --ppdb-text: #24313D;
  --ppdb-text-muted: #667085;
  --ppdb-border: #E2E5E9;
  --ppdb-control-border: #8795A3;
  --ppdb-focus: #536C82;
  --ppdb-success: #39765A;
  --ppdb-success-bg: #EAF3ED;
  --ppdb-warning: #805B29;
  --ppdb-warning-bg: #FAF0DE;
  --ppdb-error: #B45353;
  --ppdb-error-bg: #FBEFEF;
  --ppdb-radius-input: 12px;
  --ppdb-radius-card: 16px;
  --ppdb-radius-hero: 24px;
  --ppdb-shadow: 0 4px 18px rgb(38 56 74 / 6%);
  --ppdb-motion-fast: 150ms;
  --ppdb-motion-normal: 220ms;
  --ppdb-motion-slow: 320ms;
  --ppdb-ease: cubic-bezier(.2,.8,.2,1);
}
```

Scope these variables to applicant-facing UI if a shared global theme would affect other areas. Navy is the primary action color; ivory is the main background; white is the surface. Bronze is a decorative accent, not small text on white or a low-contrast button. Use semantic colors sparingly and always pair status colors with text/icons. Verify actual foreground/background contrast in the implemented UI: WCAG 2.2 AA target (4.5:1 normal text, 3:1 large text and meaningful non-text controls). Pale borders are decorative; use a stronger border when needed to identify interactive fields. Dark mode is optional and only in scope if already supported.

### Typography, spacing and primitives

- Preferred family: Plus Jakarta Sans, variable weights 400–700; fallback `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`. Manrope or Inter are alternatives if already installed. Use existing licensed/local assets or approved self-hosted WOFF2; use `font-display: swap` and avoid blocking content on fonts.
- Hero: fluid 32–52px / 1.12–1.2, weight 700. Page title: 24–32px / 1.25, 700. Section: 20–24px, 600. Card: 18px, 600. Body/input: 16px / 1.5–1.65, 400. Labels/buttons: 14–16px, 500–600. Caption: 12–14px (not for essential instructions). Use tabular numbers for counters where helpful.
- Spacing: 4, 8, 12, 16, 24, 32, 48, 64px. Page gutters: 16–20px mobile, 24–32px tablet, 32–48px desktop. Content max width approximately 1200px; long prose/form sections narrower when practical.
- Buttons/inputs: 48–52px target height; radius 10–12px. Primary solid navy, secondary outlined, tertiary text. One dominant CTA per section. Provide hover, focus-visible, active, disabled and busy states. Touch targets aim for at least 48×48 CSS px.
- Cards: radius 16px, subtle border and optional soft shadow; hero media up to 24px. Use consistent Lucide-style outline icons (prefer installed icon library); avoid replacing meaningful labels with icon-only controls.

## 4. Screen patterns

### Landing

Use an airy header with official logo and existing navigation, prominent existing registration CTA, an editorial two-column hero on desktop with existing approved school photography, and a one-column mobile layout. Reorganize existing information into readable sections for schedules, requirements, process, announcements, help and FAQ only where those contents exist. Preserve links and facts; do not fabricate content to fill a layout. Keep images responsive, with dimensions/aspect ratios reserved to prevent layout shift. Prioritize the actual hero image if it is the LCP element; lazy-load below-fold images.

### Authentication

Desktop may use a split layout (approximately 45:55) with school branding and existing imagery on one side and a focused form on the other. Mobile uses the logo and form first. Keep existing identity method, password recovery, verification, validation and navigation intact. Add accessible labels and password visibility only if compatible with the current implementation; do not introduce alternative login methods.

### Applicant dashboard

Use a restrained sidebar (240–260px) on large screens and existing navigation adapted to compact mobile navigation. Lead with existing registration status and the next available action. Show progress only if derived from actual completion state; never fake percentages. Use clear status cards, document completeness, dates and announcements where existing data supports them. Avoid analytics charts without genuine applicant need. Preserve all destinations and permissions.

### Registration forms

Use clear sections, labels above inputs, helper text and inline errors. Two columns on desktop only for short related fields; one column on mobile. Visually represent existing steps with a stepper if already present. Do not split a single form into new behavioral steps without approval; instead use section headings and existing submission behavior. Keep input names, types, masks, values, handlers and validation. Use appropriate autocomplete and inputmode only where compatible, and do not expose personal data. Preserve field state on visual transitions. Show error summary or focus the first invalid field using existing validation where possible. Sticky mobile actions must not obscure fields, error messages or the keyboard.

### Documents, status and announcements

Use accessible upload rows/cards showing real file names, permitted formats, limits, and actual upload status. Preserve file input and upload semantics. Show determinate progress only with actual progress events; otherwise use an indeterminate busy indicator. Use clear textual status and timeline where existing events exist. Preserve download/print actions and official announcement content.

## 5. Loading, animation and perceived performance

Motion must communicate state, prevent accidental duplicate actions, and improve perceived responsiveness. It must never conceal errors, delay a usable page, or be treated as a security mechanism.

| Situation | Recommended treatment |
| --- | --- |
| Initial document load | Render stable shell/critical content ASAP, with matching neutral background. Use framework-native loading boundary or server-rendered shell when supported. |
| Slow initial data | Local skeletons matching actual layout, with reserved dimensions. Keep header/navigation available when safe. |
| Route transition | Keep shell stable; use existing navigation pending state with a thin top progress indicator or local skeleton. Do not show fictitious percentages. |
| Button submit | Maintain button width, show small spinner and existing busy label; prevent duplicate actions while the real request is pending. |
| Background refresh | Keep previous valid content visible when safe, with unobtrusive refresh indicator. Never blank the entire screen unnecessarily. |
| Upload | Real progress where available; otherwise indeterminate indicator and actual status feedback. |
| Success/error | Short, subtle feedback; preserve existing error handling, retry and recovery. |

### Loading implementation rules

- Tie every loader to actual framework/router/request state. Do not use arbitrary sleep, forced splash duration, fake progress bars, or an overlay that waits for all images/fonts to load.
- Prefer progressive rendering and local skeletons. An optional brand splash (unchanged logo, ivory background, subtle indicator) is acceptable only during genuine blocking bootstrap if no useful UI can render yet. Remove immediately when usable; resolve errors through the existing error boundary/retry path. Do not add splash to every navigation.
- For short requests, optionally delay showing a visual spinner by ~120–180ms to avoid flicker, but mark the control busy and guard duplicate submission immediately. Cancel timers on resolution/unmount. Never delay completion or rely on frontend disabling for server-side idempotency.
- Avoid hydration mismatch and flash of unstyled content: use critical styles from the existing stack, stable dimensions and a consistent initial theme. Provide meaningful server/HTML fallback where the stack supports it. Never set the whole body to opacity zero pending JavaScript.
- Use `aria-busy` on the affected region, `role="status"`/polite live text for meaningful updates, and hide purely decorative spinners/skeletons from assistive technology. Do not announce animation frames. Preserve focus and avoid blocking unrelated interactions.
- Never show indefinite loading after a failed request. Clear pending state on success/error/cancel using existing request lifecycle. Use existing error messages and recovery; avoid exposing sensitive details.

### Motion specifications

Use native CSS transitions/keyframes by default; use existing animation library only when necessary. Prefer opacity and transform; do not animate layout-affecting properties on large surfaces. Recommended timings: hover/press 150ms, section/card entrance 220–320ms, dialog 180–240ms, skeleton shimmer 1.6–2s, spinner 0.8–1s. Entrances should be subtle (4–8px translation), only on noncritical content, and not delay first paint or LCP. Avoid scroll hijacking, parallax, bounce, large stagger sequences and continuous decorative animation.

Illustrative CSS; wire these classes to actual lifecycle state in the existing framework. Do not paste blindly into global styles.

```css
.ppdb-interactive {
  transition: background-color 150ms ease, border-color 150ms ease,
              box-shadow 150ms ease, transform 150ms ease;
}
.ppdb-interactive:focus-visible {
  outline: 2px solid var(--ppdb-focus);
  outline-offset: 3px;
}
.ppdb-enter { animation: ppdb-enter 220ms var(--ppdb-ease) both; }
@keyframes ppdb-enter {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.ppdb-skeleton {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  background: #E9EDF1;
}
.ppdb-skeleton::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgb(255 255 255 / 55%), transparent);
  transform: translateX(-100%);
  animation: ppdb-shimmer 1.8s ease-in-out infinite;
}
@keyframes ppdb-shimmer { to { transform: translateX(100%); } }
.ppdb-spinner {
  display: inline-block;
  width: 1em;
  height: 1em;
  border: 2px solid currentColor;
  border-inline-end-color: transparent;
  border-radius: 50%;
  animation: ppdb-spin .9s linear infinite;
}
@keyframes ppdb-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) {
  .ppdb-interactive { transition: none; }
  .ppdb-enter, .ppdb-spinner, .ppdb-skeleton::after { animation: none; }
  .ppdb-skeleton::after { display: none; }
}
```

With reduced motion enabled, retain static loading visuals and readable pending text. Never depend on `animationend` for critical application logic. Keep existing cancellation, timeout and retry behavior.

## 6. Responsive and accessibility requirements

- Mobile-first; suggested test widths 320, 360, 390, 768, 1024, 1440 CSS px. Choose breakpoints based on content, not device names. No unintended horizontal scrolling. Test at 200% zoom and keyboard-only navigation.
- Use CSS Grid/Flexbox, fluid type (`clamp()` when useful), native controls and semantic HTML. Avoid fixed height layouts that clip text. Use `100dvh` with a fallback only where needed, and safe-area insets for fixed bottom actions. Keep sticky UI clear of focus and virtual keyboards.
- Navigation may adapt from desktop sidebar to mobile bottom navigation or drawer. Preserve every existing destination; use a More menu if needed rather than dropping links. Provide accessible names and current-page state.
- Ensure heading order, form labels, visible focus, alt text, contrast, error associations, dialogs with focus management, and meaningful status announcements. Preserve usable experience without motion.
- Keep tokens platform-neutral; map them to dp/sp for future native apps rather than assuming CSS px equals native dp. Do not build a native app or install a mobile framework in this task.

## 7. Performance and privacy

Aim for Core Web Vitals at the 75th percentile: LCP ≤ 2.5s, INP ≤ 200ms, CLS ≤ 0.1. These are targets, not claims of measured performance. Use available browser tooling to test, note lab vs field results, and avoid regressions against baseline. Avoid animation libraries for trivial effects, large imagery, redundant requests and expensive backdrop filters. Reserve image/skeleton dimensions and keep essential content visible.

Never log or embed real applicant NIK, KK, addresses, passwords, documents or other personal data in screenshots, fixtures, analytics or console output. Use synthetic data for design/testing. Preserve existing authorization, CSRF protections, sanitization and upload restrictions. Report discovered security issues separately; animation does not protect a website from attacks.

## 8. Definition of done

- All original content and controls remain accessible; original routes, data contracts and business behavior are preserved.
- Landing, login, dashboard, forms and existing applicant views look coherent with the Calm Academic tokens, including mobile layouts.
- Loading is bound to real state, never artificially delays the user, and handles errors and reduced motion.
- Navigation, form values, validation, submit, upload, auth and status flow are regression-tested where possible with synthetic data; no duplicate submission induced by UI changes.
- No unintended admin redesign, new dependency, network tracking, fabricated content or unrelated code changes.
- Build/lint/typecheck/tests run as supported by the repository; disclose failures or unavailable checks.
- Final report in Indonesian unless requested otherwise: (1) design changes, (2) files changed, (3) functional preservation and checks, (4) known limitations, (5) optional recommendations requiring approval. If uncertain about business rules, leave them intact rather than guessing.

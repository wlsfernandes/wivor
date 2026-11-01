# WiVor Photos — Master Frontend UX & Visual Design Specification

**Status:** APPROVED DESIGN DIRECTION — implementation instructions for local AI  
**Project:** WiVor Photos (`https://wivorphotos.com`)  
**Audience:** Local coding AI / frontend developer  
**Mandatory specialist:** `/frontend-design` agent  
**Scope:** Public-facing frontend UX, visual presentation, responsive behavior, and accessibility. **Do not rebuild backend features.**  
**Design system version:** 1.0 — November 2026

---

## 0. READ FIRST — EXACT WORK ORDER FOR THE LOCAL AI

> **Before changing any code, invoke the `/frontend-design` agent and ask it to lead the visual-design and UX implementation.** Use the existing repository and its established Laravel frontend architecture. Do not improvise a new visual identity or generate a generic AI-style landing page.

**Agent invocation:** Use `/frontend-design` in the local AI environment if available. Do not claim it was used unless it actually ran. If unavailable, report that plainly and follow this document as the binding design brief.

**Required sequence:**

1. **Inspect before editing.** Identify the actual Laravel version, rendering method (Blade/Livewire/Inertia/Vue/etc.), CSS framework, asset pipeline, layouts, public views, components, page routes, forms, controllers and JS behavior. Document the findings briefly. Do not assume filenames.
2. **Build the main design foundation first.** Define design tokens, load fonts, establish a public-facing design stylesheet or theme layer, and build/refine the shared visual components. Apply these consistently to public header, footer, typography, buttons, containers, cards, forms and navigation. Preserve working UI behavior.
3. **Establish a single visual reference.** Create or implement the **homepage** using that main design system. Review it at desktop and mobile widths. Treat this implementation as the source of truth for every subsequent public page.
4. **Continue page by page using the same system.** Event discovery -> event detail/search -> gallery/selection -> photographers -> organizers -> how it works -> FAQ/about/contact -> other public pages. The per-page design briefs below are subordinate to the master design tokens and components.
5. **Verify every step.** Run relevant tests/builds and inspect the page in a browser if tooling permits. Compare screenshots before/after; check 390px mobile, tablet and desktop. Document limitations if screenshots/browser access are unavailable.
6. **Do not silently change functionality.** If a proposed UX change requires modifying controllers, models, DB structure, payment logic or privacy behavior, stop that subtask, explain it and propose the smallest separate change. Keep progressing on unrelated frontend work.

**Definition of success:** WiVor looks like a carefully art-directed, premium-yet-approachable sports-photo marketplace. It feels active and trustworthy; users understand immediately how to find their race photos. The experience is clearly _not_ a newspaper, a generic startup template or a flashy AI-generated website.

---

# PART A — PERMANENT MAIN DESIGN SYSTEM

## 1. Brand and experience foundation

### 1.1 Brand position

**WiVor Photos connects weekend athletes with photographs from the events they competed in, while giving event photographers a professional way to publish and sell their work.**

- **Primary user:** Recreational/weekend runner, triathlete, cyclist, or local-race participant arriving from a phone or an event link.
- **Secondary user:** Sports-event photographer who wants to publish images, reach customers and understand earnings.
- **Tertiary user:** Event organizer evaluating a photo partner.
- **Primary visitor task:** Find an event -> find my photos -> preview -> select -> purchase/download.
- **Brand personality:** Modern, athletic, human, precise, calm, reliable.
- **Visual equation:** Sports energy **from authentic photography** + customer confidence **from a restrained, professional interface**.

### 1.2 Binding design direction

**Direction A: Modern Athletic** — approved.

- Mostly light/off-white backgrounds.
- Deep charcoal typography and dark hero areas used deliberately.
- One restrained warm-orange call-to-action accent.
- Large authentic sports-event imagery, not synthetic athlete photos.
- Strong, contemporary _sans-serif_ type, no newspaper/editorial-serif feeling.
- Generous spacing and disciplined alignment.
- Clean controls, clear information hierarchy and minimal ornament.
- Product photography/gallery pages let the real photos take visual priority.

### 1.3 Explicitly avoid

- Serif display fonts or editorial/newspaper headline treatment.
- Gradient overload, blurred multicolor backgrounds, floating glassmorphic cards.
- Huge shadows, excessive rounded corners, repeated icon cards, stock illustrations.
- Generic copy like "revolutionary AI-powered platform" or fabricated growth numbers.
- Large carousel heroes, autoplaying motion or decorative animation.
- Emoji as icons, massive iconography and crowded navigations.
- Reusing one identical marketing hero layout on every page.
- Fake partner logos, testimonials, ratings, events, galleries or pricing.
- Invented event images / generated faces / altered images of real athletes.
- Changing a feature merely to make the screenshot look cleaner.

### 1.4 Voice and microcopy

- Direct, short, friendly and practical. Avoid hype.
- Speak to **the athlete**, not the technology: "Find your race photos" beats "Experience AI image discovery."
- Explain bib and selfie options plainly and preserve required biometric disclosures.
- Preferred name in public UI: **WiVor** in normal brand usage; use **WiVor Photos** where a descriptive brand label is clearer. Standardize current mixed "WiVor"/"WivorPhotos" presentation **only where it is public copy**; don't rename models, domains, legal entities or identifiers.
- Never promise "instant" results or privacy guarantees beyond actual application behavior.
- All visible claims regarding fees, commissions, refunds, discounts and photo counts must match verified existing system logic.

---

## 2. Color system — canonical source of truth

Implement via existing CSS variables / Tailwind theme / design tokens, whichever fits the repo. **Do not hardcode slightly different shades page by page.**

| Token                    | HEX       | Usage                                                    |
| ------------------------ | --------- | -------------------------------------------------------- |
| `--wivor-ink`            | `#151B22` | Headings, key text, dark backgrounds                     |
| `--wivor-ink-soft`       | `#28323D` | Secondary dark surfaces                                  |
| `--wivor-text`           | `#34404C` | Body copy                                                |
| `--wivor-muted`          | `#596675` | Secondary descriptions / labels; verify context contrast |
| `--wivor-bg`             | `#F7F8F6` | Main off-white background                                |
| `--wivor-surface`        | `#FFFFFF` | Cards, controls, primary white sections                  |
| `--wivor-surface-subtle` | `#F1F3F3` | Alternate light section backgrounds                      |
| `--wivor-border`         | `#E1E6E7` | Dividers, borders, input outlines                        |
| `--wivor-accent`         | `#C64526` | Main CTA, important active states, limited brand accents |
| `--wivor-accent-hover`   | `#A5381C` | Hover/pressed CTA                                        |
| `--wivor-accent-subtle`  | `#FFF0EA` | Warm highlight background, selected surfaces             |
| `--wivor-on-dark`        | `#F7F8FA` | Headings on dark areas                                   |
| `--wivor-on-dark-muted`  | `#DFE4E8` | Supporting text on dark areas                            |
| `--wivor-success`        | `#1D7755` | "Photos available" and success states                    |
| `--wivor-focus`          | `#1174B8` | Visible keyboard focus, never removed                    |
| `--wivor-error`          | `#B42318` | Form errors / validation                                 |

**Required usage rules:**

1. Pages should usually be **80–90% visually neutral** (white, off-white, charcoal, actual images); orange is the interactive accent, not the page background.
2. Keep primary text on light backgrounds dark; avoid weak gray text.
3. Primary CTA: orange background `#C64526` with white label; this pairing has approximately **4.9:1** contrast. Validate in actual rendered states.
4. Dark hero sections: `#151B22` with white/off-white type. Don't place light gray text at reduced opacity until contrast is checked.
5. Success badges use green only for real photo availability; **never imply "photos ready" for events with no gallery**.
6. Avoid status meaning conveyed by color alone: include clear labels.
7. Do not add more "brand colors" without approval. Sports photos already provide visual variety.

### 2.1 CSS token example (adapt to existing architecture)

```css
:root {
    --wivor-ink: #151b22;
    --wivor-ink-soft: #28323d;
    --wivor-text: #34404c;
    --wivor-muted: #596675;
    --wivor-bg: #f7f8f6;
    --wivor-surface: #ffffff;
    --wivor-surface-subtle: #f1f3f3;
    --wivor-border: #e1e6e7;
    --wivor-accent: #c64526;
    --wivor-accent-hover: #a5381c;
    --wivor-accent-subtle: #fff0ea;
    --wivor-on-dark: #f7f8fa;
    --wivor-on-dark-muted: #dfe4e8;
    --wivor-success: #1d7755;
    --wivor-focus: #1174b8;
    --wivor-error: #b42318;
}
```

Do **not** create this exact block if the existing project already provides a token configuration; integrate using the repo's conventions instead.

---

## 3. Typography — canonical source of truth

### 3.1 Font families

- **Headings / hero / meaningful display labels:** **Manrope**.
- **Body / UI / navigation / buttons / cards / forms:** **Inter**.
- Fallback for both: `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`.
- Use practical font weights only: Manrope `500, 600, 700, 800` (800 sparingly); Inter `400, 500, 600, 700`.
- Load through existing asset pipeline with efficient subsets/weights; optimize for speed. Self-host if appropriate and legally licensed, otherwise load from approved font source.
- **No serif fonts** in the public marketing and photo-purchase journey.

### 3.2 Desktop type scale

| Usage                   | Desktop                               | Mobile  | Weight / leading             |
| ----------------------- | ------------------------------------- | ------- | ---------------------------- |
| Hero H1                 | `clamp(2.5rem, 5vw, 4rem)` (~40–64px) | 36–42px | Manrope 700–800; `1.08–1.14` |
| Main page H1            | 44–52px                               | 32–38px | Manrope 700; `1.12`          |
| H2 / sections           | 36–42px                               | 28–32px | Manrope 700; `1.16–1.22`     |
| H3 / cards              | 22–26px                               | 19–22px | Manrope 600–700; `1.25–1.35` |
| Lead / hero description | 18–20px                               | 16–18px | Inter 400; `1.55`            |
| Regular body            | 16px                                  | 16px    | Inter 400; `1.55–1.65`       |
| Navigation              | 14–15px                               | 15–16px | Inter 500–600                |
| Buttons / inputs        | 15–16px                               | 16px    | Inter 600                    |
| Metadata / supporting   | 13–14px                               | 13–14px | Inter 400–500                |

**Typeface rules:**

- Maintain clearly differentiated headings without extreme weight or overlarge size.
- Hero H1 should be about **8–12 words maximum when feasible**, using meaningful line breaks, not massive full-width wrapping.
- Avoid all caps for headings. Small uppercase eyebrow labels are okay _sparingly_.
- Headings slightly tight letter spacing (`-0.025em` to `-0.04em` when appropriate), body normal.
- Limit long descriptive text to ~62–75 characters per line; meaningful section text usually <= 60–65 characters.
- Use consistent numeric formatting for prices and counts; align visually where relevant.
- Underline text links on hover/focus and where clarity requires; don't rely on color only.

---

## 4. Layout, spacing, sizing and shape

### 4.1 Spacing scale (4px base)

Canonical spacing units, px: **4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96, 112**.

- General desktop section vertical padding: **80–96px**; major opening/closing blocks may be 104–112px.
- Mobile sections: **48–64px**.
- Content width: **1240px** maximum for wide content, **720–800px** for reading columns.
- Desktop container horizontal padding: **24–32px**; tablet **24px**; mobile **16–20px**.
- Cards: **20–28px** internal padding; tight mobile cards **16–20px**.
- Page section heading -> supporting copy: **12–16px**.
- Heading/intro -> grid: **32–40px** desktop, **24px** mobile.
- Grid gutters: **20–24px** desktop, **12–16px** mobile.
- Button/icon internal gap: **8px**.
- Form field vertical stack gap: **16–20px**.

### 4.2 Radius, borders and elevation

| Element                              | Radius                                  |
| ------------------------------------ | --------------------------------------- |
| Primary/secondary buttons            | `10px`                                  |
| Text/search inputs                   | `10px`                                  |
| Cards                                | `14px`                                  |
| Photo thumbnails / tiles             | `10–12px`                               |
| Major panels / hero image containers | `16–20px`                               |
| Chips / pills                        | Full round only for actual chips/badges |

- Prefer **subtle 1px borders** rather than shadows.
- If required, use a restrained hover shadow, never a huge floating-card shadow.
- Keep image edges crisp and clean. Don't round every surface excessively.
- No ornamental blobs, decorative curves or pseudo-3D effects.

### 4.3 Breakpoints and behavior

Use existing repo's established breakpoints where practical; intended UX targets:

- Small mobile: **360–390px** (must fit with no horizontal scroll).
- Mobile: up to ~639px.
- Tablet: ~640–1023px.
- Desktop: >=1024px; wide desktop QA at **1440px**.
- Navigation: compact mobile menu at appropriate breakpoint; desktop nav uses clean horizontal alignment.
- Touch targets: minimum **44 x 44px** where practical; primary actions/inputs **48–52px** tall.
- Buttons must not become two-word vertical stacks on small screens.
- Gallery: adapt 2 columns mobile / 3 tablet / 3–4 desktop based on image sizing, without awkward micro-thumbnails.
- No hidden critical text, no carousel-only access to important content.

---

## 5. Shared frontend component contract

Build/refine **one** public visual language. Prefer existing Blade components / component conventions over duplicating markup or adding a new JS framework.

### 5.1 Public header/navigation

- Desktop header approximate height **72–76px**; mobile **60–64px**.
- Clear WiVor logo/wordmark at left; preserve existing brand assets where viable.
- Primary navigation: `How It Works`, `Athletes` / `Find Photos`, `Photographers`, `Organizers`, possibly FAQ depending on space.
- Keep a visible, prominent `Find My Photos` primary CTA on desktop.
- Log in remains accessible but visually secondary.
- On mobile, logo + clear search/find CTA + accessible menu button if space allows; avoid crowded links.
- Menu must be keyboard operable, show its open state, close predictably, and not obscure key actions.
- Add subtle bottom divider or mild background, not noisy drop shadows.
- Use sticky header only if it helps and does not occupy too much mobile viewport.

### 5.2 Buttons and links

- **Primary:** orange fill, white Inter 600 text, 48–52px tall, 10px radius, straightforward label.
- **Secondary:** white/transparent background, dark text, subtle border.
- **Tertiary:** clear inline text link with subtle arrow where useful.
- Disabled/loading/hover/focus states must be explicit and consistent.
- Use verbs: `Find My Photos`, `Search Events`, `View Photos`, `Browse Gallery`, `Apply as Photographer`.
- One dominant primary CTA per meaningful viewport/section, not competing orange buttons everywhere.
- Ensure destructive actions and payment submission cannot be triggered by accidental link styling.

### 5.3 Search bar

- A standout functional search entry point for event/city/sport.
- Search icon, persistent accessible label, helpful placeholder, submit action.
- Mobile full-width; do not force users through 6 filters before seeing events.
- Existing filters remain accessible via expandable `Filters` panel / responsive secondary row.
- Preserve actual query/form semantics, URL parameters, state and result handling.

### 5.4 Event card

- Reusable responsive component with actual event name, date, location, sport, photo availability and event link.
- Prefer real event cover photo **only if already available and permitted**; otherwise use a professional neutral fallback with sport label (not a fake race photo).
- Distinguish `Photos available`, `Upcoming`, and `Past / no live gallery` semantically.
- Show actual count only when backed by data; never hardcode 1,445 or 5,179 in app code.
- Entire card can be navigable if accessible and does not create nested click conflicts.
- Photo-first cards on desktop; compact list/card hybrid okay on mobile.

### 5.5 Forms

- Real labels visible; never placeholder-only inputs.
- Input border `--wivor-border`, min height 48px, white surface, 10px radius.
- Helper text directly below relevant field.
- Field-level errors and success messages have appropriate text + icon/context, not color alone.
- Loading states and disabled states remain tied to actual JS/form lifecycle.
- No changing `name`, IDs, `action`, method, CSRF tokens, validation, autocomplete, upload restrictions or webhook-related behavior unless necessary and separately validated.
- Forms must survive validation redirects with entered data and server errors.

### 5.6 Badges / statuses

- `Photos available` or `Photos ready`: verified live gallery only.
- `Coming soon`: published upcoming event without photo gallery.
- `No matches`: not a failure; provide actionable next search path.
- Avoid misleading availability, urgency, sale and pricing badges.

### 5.7 Footer

- Compact organized columns: Platform, For You, Company, Legal.
- Preserve links to Privacy, Terms, Biometric Notice, Refund Policy, Photographer Terms and Photo Removal Request.
- Maintain attribution/legal text already required; do not silently remove it.
- Reasonable spacing, soft borders and a simple copyright row.

### 5.8 Micro-interactions

- 120–240ms transitions for border, background, opacity and mild translate (1–2px max).
- No autoplay hero videos, bouncy entrance animation or unnecessary parallax.
- Exception: the homepage hero copy has one entrance on page load (fade, 12px rise, accent rule, under 700ms). No other page or section animates on entry.
- Honor `prefers-reduced-motion: reduce`.
- Keep focus indicators visible and error/loading states stable (avoid layout jumping).

---

## 6. Photography and media direction (non-negotiable)

**The product is the imagery. Real event photography is the strongest design asset.**

- Use existing licensed WiVor images/event covers from the app and existing assets.
- Prefer real local races, finish-line emotion, bib-number athletes, triathlons, cycling and photographers in action.
- Image selection should feel documentary/authentic, not polished stock ad photography.
- Do not generate fake race images, fabricate event galleries, use famous athletes without rights or pull third-party images without permission.
- If real imagery is missing, use neutral designed fallback, **not** misleading stock imagery presented as an event.
- Keep meaningful `alt` text; decorative imagery may have empty alt.
- Respect photography rights, attribution obligations, watermarks and original/full-resolution security.
- Hero images: modern crop with visible human story. Avoid high-contrast overlays obscuring faces.
- In photo gallery **never alter originals, watermarks, delivery URL logic, purchase protection or photographer-owned image content**.
- Use existing image delivery/CDN where possible; optimize `srcset`, sizes and lazy-loading without introducing broken image URLs.
- Avoid lazy-loading the above-the-fold hero image if it harms largest contentful paint.

---

## 7. Accessibility, mobile and performance guardrails

- Work toward **WCAG 2.2 AA** for the redesigned UI.
- Body text and button contrast >=4.5:1; large text >=3:1; controls/visible state indicators >=3:1 where applicable.
- Semantic headings: one H1 per page, logical H2/H3 hierarchy; link/button semantics must reflect behavior.
- Keyboard navigation across nav, search, filters, gallery, lightbox, forms and photo-selection.
- Visible focus ring ~3px in `--wivor-focus`, with adequate offset and contrast.
- Dialog/lightbox: focus management, `Esc` close, scroll handling and screen-reader labels if implemented.
- Do not make selfie submission possible without the existing required consent.
- Support form error messaging, image loading/error states and empty search states.
- Maintain responsive image aspect ratio / reserve space to minimize layout shift.
- Avoid unnecessary JS packages and duplicate CSS libraries; maintain bundler conventions.
- Aim for Core Web Vitals targets under realistic conditions (LCP ~2.5s or better, CLS <=0.1, INP <=200ms), but **measure rather than claim achievement**.

---

# PART B — PAGE-BY-PAGE DESIGNS THAT MUST FOLLOW PART A

**Important implementation rule:** Part A is the controlling specification. Before changing each page, **reuse** shared components, tokens, heading hierarchy and spacing. Do not create a new design per page. Complete pages in the following order. Preserve the working URLs/routes discovered in the repository; sample public URLs below are for orientation only.

## 8. Page 1 — Homepage `/` (design reference / first implemented page)

### Goal

Within 3–5 seconds, a new athlete understands **what WiVor does** and **how to find race photos**. Photographer and organizer paths remain accessible without distracting from the primary action.

### Layout / content hierarchy

1. **Header:** clean standardized site navigation, primary find-photos action.
2. **Hero:** a controlled dark-charcoal section, ideally split approximately 48/52 text/image on desktop. Use authentic race photograph on right (or appropriate existing hero asset). Mobile stacks copy, primary search/action, then image.
3. **Eyebrow (optional):** `EVERY MOMENT COUNTS` — use sparingly, not everywhere.
4. **Hero H1:** recommended `Your race. Your moment.` Alternative existing line `Your best moments. Found in seconds.` may be retained if it better fits final layout. Do not rewrite all copy needlessly.
5. **Hero paragraph:** one short outcome-focused explanation: find your event, search with a bib number or selfie, preview and purchase the photos you want.
6. **Immediate functional event search:** one large field connected to **existing event search route**, plus `Search Events` button. If making that functional requires unsupported backend changes, use a clear direct CTA to the existing event discovery page rather than building a fake field.
7. **Photos-available events:** showcase real live galleries from existing data, with real event photos/names/counts only if the data is available in the existing view. If dynamic integration would require new controller/service work, keep an existing event CTA and note the follow-up; **no hardcoded fake event cards**.
8. **How it works:** only three concise steps: Find event -> Find yourself -> Choose photos.
9. **For photographers / organizers:** two differentiated visual feature sections/cards; concise promise and one CTA each.
10. **Practical reassurance:** preview-before-purchase, selection, privacy notice (accurate to app), real pricing clarity where meaningful.
11. **Closing CTA + footer:** direct back to event discovery; no excessive repeating sections.

### Visual composition

- Hero should be **photo-led**, not a giant block of generic text.
- Dark charcoal is an accent region, **not** an excuse to make the whole site dark.
- Main page transitions to clean off-white backgrounds and real images.
- Use restrained section rhythm: hero -> search -> gallery highlights -> explanation -> partner sections -> footer.
- Avoid repeating "free", "smart matching" and "buy only what you love" across multiple near-identical card grids.
- Real mobile layout must be deliberate at 390px, not merely desktop columns stacking with huge gaps.

### Acceptance checklist

- [ ] Hero + CTA understandable within first viewport on common phone sizes.
- [ ] Search/CTA reaches real event search with no dead action.
- [ ] Fonts, colors, spacing, buttons match Part A.
- [ ] No fabricated event cards/photo assets/metrics.
- [ ] Header, footer, login and all legal links still work.
- [ ] Mobile layout polished and no overflow at 360/390px.

---

## 9. Page 2 — Find Your Event / event discovery `/list-events`

### Goal

Let people find the correct event in one or two actions and distinguish events with photos from upcoming events.

### Existing business context to preserve

Current public page has event/city/sport search, state/city/sport filters, from/to dates, grouped sections for live/upcoming/past. Preserve the functional filters, result logic, route and event states.

### Redesign

- Compact white/light hero: `Find your race photos` and one short explanation.
- Prominent event/city/sport search bar, large enough for mobile touch.
- `Filters` collapsible secondary control with state, city, sport and date controls; preserve values after submission.
- Results section visually starts with **photos-available events** where backend already provides them; upcoming and past sections follow logically. Don't misclassify status.
- Use shared `EventCard`, actual covers if present, sport/date/location, live photo counts if available, clear CTA.
- `Photos available` success treatment is reserved for actual published galleries.
- Offer a helpful empty-results state: `No events matched. Try another name, city or date.` + clear filters action.
- If filters are server-driven, keep traditional form submission; don't introduce unneeded SPA state.
- Preserve pagination if it exists; only visually improve it.

### Acceptance

- [ ] Every existing filter still submits the correct parameters and preserves its selection.
- [ ] Results, event statuses and pagination unchanged in meaning.
- [ ] No event is falsely shown as having photos.
- [ ] Tapping cards opens correct existing event.
- [ ] Filter drawer and mobile keyboard flow work.

---

## 10. Page 3 — Event detail + photo discovery `/events/{slug}`

### Goal

Make bib-number search, selfie search and browse-all obvious, understandable and safe.

### Existing details to respect

Example events presently show event cover, sport, date, venue/location, actual photo count, per-photo price and volume discount. They have selfie search with JPEG/PNG size restriction and explicit privacy consent; bib search; browse-all gallery link. These are **dynamic business requirements**, not decorative content.

### Redesign

1. Event overview: real cover, breadcrumb/back to events, sport label, H1, date, location/venue.
2. Clean event info band: number of available photos, actual base price, actual existing quantity discount where applicable. Use real backend values and currency formatting.
3. Heading: `Find yourself in the photos` + `Choose the easiest way to search this event.`
4. Two concise search methods, using matching shared form components:
    - **Bib number:** visually primary for straightforward text entry, clear label, existing form route/validation.
    - **Selfie search:** upload field/dropzone, accepted JPEG/PNG and actual file-size restriction, existing explicit unchecked opt-in, visible privacy notice and link, search submission and loading state.
5. Secondary full-width link/action: `Or browse all photos` / `Browse Gallery`.
6. About event description if present; don't invent descriptive copy for empty events.
7. Preserve all legal/privacy links and any event-organizer attribution if present.

### Safety and behavior

- **Never auto-enable, precheck or hide biometric consent.**
- Preserve the actual application text if it makes specific technical claims about Amazon Rekognition or retention; review with the developer before rewriting them.
- Don't introduce a new selfie-preview storage mechanism.
- Preserve CSRF, route method, validation and upload limits (currently public UI says JPEG/PNG up to 5MB; confirm in code).
- Do not restructure the page such that required notices are offscreen or disconnected from the upload action.

### Acceptance

- [ ] Bib search behavior and errors identical to original (only appearance improved).
- [ ] Selfie restrictions, privacy statement and explicit consent still enforced.
- [ ] Counts/prices/discounts display live correct values.
- [ ] Browse-all link routes to existing gallery.
- [ ] Mobile search interface is clear and not cramped.

---

## 11. Page 4 — Photo results / gallery / selection / checkout-adjacent UI

### Goal

Photos are the star. Finding, previewing, selecting and buying them feels fluid on phone and desktop.

### Redesign

- Compact event identity at top: back link, event name, gallery/result title, photo count if appropriate.
- If search-filtered, identify context simply (`Results for bib #...`, `Your photo matches`) only when supported by data.
- Responsive photo grid: **2 columns mobile; 3 tablet; 3–4 desktop** depending on actual source aspect ratio, with 12–20px gutters.
- Treat watermarked previews as customer-facing product images; **do not remove watermarks**.
- Show a consistent selected/favorite visual state, hover/focus feedback, and a clear action to select or deselect.
- If existing JS supports lightbox, improve presentation and navigation; if not, do not replace core gallery with an untested new library.
- Large preview must preserve image proportions and avoid misleading crop. Grid may use configured thumbnails; actual preview should contain the full image.
- Mobile purchase summary: compact sticky bottom area **only if** it does not overlap gallery controls or navigation; show count and dynamically computed price/discount from **existing pricing logic**.
- Ensure gallery pagination/next page/lazy loading, selections and checkout transitions remain correct.
- Empty-result state: helpful message and alternate routes (try bib, try selfie with consent, browse full gallery) depending on current page/search context.
- Never alter purchased image delivery URL, protected file handling, receipts, discount computation, payment processing or authorization checks.

### Acceptance

- [ ] Selection state survives intended navigation/checkout as before.
- [ ] Totals and discounts match original backend logic exactly.
- [ ] Full previews do not distort photos; watermarks remain.
- [ ] Mobile gallery usable with thumb and keyboard/screen reader.
- [ ] Empty/loading/failed-image states handled clearly.
- [ ] No regression in checkout and protected downloads.

---

## 12. Page 5 — Photographers `/photographers`

### Goal

Attract real photographers, explain value and responsibilities, and make existing application easier to complete.

### Current functional content to preserve

The page explains application, approval, coverage, upload, sales and earnings; it also has an applicant registration form with identity/contact, portfolio/equipment, biography, password, adult confirmation, photographer terms and email verification.

### Redesign

- Hero photo of a **real event photographer** if approved asset exists.
- Suggested H1: `Capture the moment. We handle the rest.` with a short honest supporting line.
- Clear action: `Apply as a Photographer` scrolls/links to **working** application.
- Brief differentiators: showcase photos, reach athletes, protected galleries/checkout, track earnings (only actual capabilities).
- Condense the six-step explanation into an elegant stepper/timeline or concise two-row process; preserve details via expandable content or linked documentation as appropriate.
- Explain no-upfront-fee vs commission accurately. Do not claim "no fees" if sales are subject to commission.
- Form: keep existing fields, terms checkbox, age confirmation, validation, email verification and security. Improve grouping, label clarity and visual hierarchy.
- Avoid making the general contact form compete with application CTA; place support link/section at the bottom if already present.
- When possible, show actual photographer work only with permission and proper context.

### Acceptance

- [ ] Applicant fields, validation, passwords and consent remain intact.
- [ ] Registration submission/email verification unaffected.
- [ ] No misleading fee/commission claims.
- [ ] Primary CTA scrolls/routes correctly.
- [ ] Mobile long form readable and usable.

---

## 13. Page 6 — Organizers `/organizers`

### Goal

Show event directors that WiVor improves the athlete experience without adding unnecessary work to their team.

### Redesign

- Clean athletic visual intro, with actual running-event imagery if licensed.
- Suggested H1: `Give your athletes photos worth remembering.`
- Brief value section: enhanced athlete experience, photographers coordinated, event promotion through sharing, and accurate partnership economics.
- Three-step process: Tell us about event -> WiVor coordinates photographers -> athletes find and buy photos.
- One clear `Become a Partner` action to the existing contact/lead path.
- No fake organizer logos, fabricated testimonials or unsupported claims like guarantees of zero effort.
- Reuse same font/color/spacing/buttons; do **not** simply clone photographer hero.

### Acceptance

- [ ] Partnership CTA leads to functional form/contact.
- [ ] No inaccurate promise regarding price, support or workload.
- [ ] Page feels part of same brand as homepage.

---

## 14. Page 7 — How It Works `/how-it-works`

### Goal

Explain the full flow quickly without marketing-card repetition.

### Design

- Light intro with simple H1 and short lead.
- Athlete flow: Find event -> bib/selfie search -> preview -> buy/download.
- Photographer flow: apply -> get approved -> photograph/upload -> sell/track earnings (reflect actual workflow).
- Use concise numbered steps with alternating balanced layout or a neat two-column composition, not 8 identical giant cards.
- Include clear CTA after each audience section.
- Link to privacy/biometric notice next to selfie explanation.
- Keep technical terminology secondary, only where needed to understand consent.

---

## 15. Page 8 — FAQ, About, Contact and other public utility pages

### FAQ

- Readable question/answer content, narrow body width, accessible accordions if existing UI supports them.
- Questions grouped logically by athletes / purchases / photographers / privacy.
- Preserve answer meaning and legal commitments.

### About

- A human, credible story; concise real facts, not invented company history/team portraits.
- Use actual brand identity and authentic photography where available.

### Contact

- Clean form labels/fields, visible confirmation and validation, accurate expected contact route.
- No removal of spam protection, CSRF or existing backend behavior.

### Login / password reset / other public auth UI

- Apply shared typography, buttons, fields, spacing and color.
- **Do not change authentication rules, routes or security.**

### Legal pages (Privacy / Terms / Biometric Notice / Refund / Photographer Terms / Photo Removal)

- Apply readable type/spacing, consistent header/footer and narrow text column.
- **Do not rewrite legal content, change wording, remove required links or modify consent requirements.**
- Ensure links and forms remain reachable from the new footer and relevant workflows.

---

# PART C — ENGINEERING CONSTRAINTS, QA AND HANDOFF

## 16. Laravel implementation guardrails

**Frontend design work only.** This instruction is not authorization for backend refactoring.

- Inspect and reuse the existing architecture. If it is Blade, use Blade; if Livewire/Inertia/other is present, work within it.
- Reuse existing CSS framework, components, build process, controllers, routes and JavaScript behavior.
- Avoid introducing React, Vue, animation libraries or large UI kits just to reproduce a standard layout.
- No schema changes, migrations, model changes, payment changes, queued jobs, storage changes or new APIs solely for design.
- No changes to event administration, participant systems, CRM, photographer admin dashboards or super-admin areas under this brief.
- Preserve named routes, existing internal route links, parameter handling, authentication checks, CSRF, file input configuration and security policies.
- Scope visual class names/styles to public pages if admin shares base CSS. **Avoid breaking dashboard/admin with global resets.**
- If same component is shared between public and admin, isolate changes or use a public variant rather than making broad risky modifications.
- Do not remove SEO title/description, canonical metadata or structured data already present; preserve headings and meaningful alt texts.
- Avoid duplicated one-off CSS rules by using reusable tokens/components.
- Keep existing event search, recognition, photo selection, checkout, delivery and application workflows fully functional.

### 16.1 File/component organization (adapt, do not assume existing paths)

Proposed responsibilities only:

```text
Public theme tokens                  -> existing CSS variables or theme config
Public typography / layout           -> existing base public stylesheet
Header, footer, buttons, inputs      -> current reusable component/pattern system
Event card / status / event search   -> reusable public components or partials
Gallery tile / selection controls    -> existing gallery UI layer with improved style
Page-specific spacing/composition    -> scoped public page styles / views
```

Avoid creating a parallel design system disconnected from the rest of the repo.

---

## 17. Mandatory cross-page consistency checklist

Run after every page and at final completion:

- [ ] All public pages use **Manrope for headings, Inter for UI/body**.
- [ ] Approved HEX values used via common tokens; no random accent colors.
- [ ] Same button heights, corner radius, focus behavior and font weight.
- [ ] Same container widths, section rhythm and typography scale.
- [ ] One clear H1 and sensible heading order per page.
- [ ] All photos use real authorized assets or honest fallback.
- [ ] Event listing and event-detail cards share coherent visual patterns.
- [ ] Athlete journey and photographer journey have visually distinct but branded emphasis.
- [ ] No "generic AI" gradients, fake icons, animation or repetitive template sections.
- [ ] White/light overall brand with selective charcoal emphasis; orange reserved for action.
- [ ] Real mobile QA completed and no horizontal scrolling.
- [ ] Existing routes, dynamic variables, photo counts, privacy, prices, forms and payment behavior preserved.

---

## 18. Minimum test matrix

Test after each stage (automated tests where available, manual verification where not):

| Test area                 | Required check                                                                                   |
| ------------------------- | ------------------------------------------------------------------------------------------------ |
| Home/navigation           | Hero CTA, event search, mobile menu, login, partner links                                        |
| Search                    | Search text, state/city/sport/date controls, reset, no-results, populated results                |
| Event detail              | Existing event cover, correct location/count/price/discount, bib search, selfie file and consent |
| Photo results             | Open preview, select/deselect, paginate, correct totals, checkout path                           |
| Photographer registration | Required fields, errors, password rules, consents, submission, verification flow                 |
| Organizer lead            | CTA and actual contact/lead submission                                                           |
| Legal                     | Privacy, biometric notice, refund, photo removal and terms reachable                             |
| Responsive                | 360px, 390px, 768px, 1024px, 1440px and at least one landscape mobile scenario                   |
| Accessibility             | Keyboard flow, focus states, contrast, labels, reduced-motion mode                               |
| Build                     | Existing frontend asset build successful, no JS console errors, no broken images                 |

**Use existing test fixtures/events or a safe local/staging environment.** Avoid real purchases, outbound user notifications, uploads to production or destructive operations during visual QA unless expressly authorized.

---

## 19. Implementation phases and stopping points

### Phase 0 — Audit and plan

- [ ] Identify actual public view files, layout/components, CSS/JS architecture.
- [ ] Note current font loading and all conflicting CSS declarations.
- [ ] List all public pages and workflow-critical buttons/forms.
- [ ] Capture baseline screenshots if browser tooling allows.
- [ ] Give a short planned file-change list.

**Deliverable:** concise current-state map and exact change plan. **Then proceed to Phase 1.**

### Phase 1 — MASTER DESIGN FOUNDATION (must precede page work)

- [ ] Create canonical design tokens and fonts.
- [ ] Standardize public header/navigation and footer.
- [ ] Standardize buttons, inputs, cards, badges, containers and focus states.
- [ ] Ensure new styles don't leak into admin interfaces.
- [ ] Build homepage as the main visual reference.
- [ ] Validate at 390px and desktop; run build/lint/tests available.

**Deliverable:** coherent, implemented master design and first finished homepage. **All later work must follow this foundation.**

### Phase 2 — Athlete discovery

- [ ] Redesign `/list-events` using shared event-card/search components.
- [ ] Verify all existing filters and event states.

### Phase 3 — Event search & gallery

- [ ] Redesign `/events/{slug}` with bib, selfie + existing consent and browse path.
- [ ] Improve existing gallery/result views with real photo-first layout.
- [ ] Preserve all photo selection/purchase behavior.

### Phase 4 — Photographer and organizer pages

- [ ] Redesign photographers and application presentation.
- [ ] Redesign organizers and partnership contact flow.

### Phase 5 — Supporting public pages

- [ ] How it works, FAQ, About, Contact, auth pages and public legal page _layout only_.
- [ ] Unified responsive and accessibility audit.

### Phase 6 — Final regression and handoff

- [ ] Inspect desktop/mobile screenshots of every primary page.
- [ ] Run test/build commands and fix regressions from this work.
- [ ] Ensure no admin/backend changes slipped in.
- [ ] Produce final changed-file inventory, screenshots/notes and outstanding items.

**Do not treat each phase as permission for an independent redesign. Part A stays canonical.**

---

## 20. Required reporting format from the local AI

After each phase, report:

```text
Phase:
/frontend-design agent used: YES / NO (reason if NO)
Files changed:
Shared design-system elements reused:
Visual improvements completed:
Existing behaviors tested and preserved:
Viewport screenshots/visual checks (or reason unavailable):
Build/tests executed + results:
Known limitations / follow-up questions:
Next page in sequence:
```

If design decisions are ambiguous, **choose the smallest change consistent with Part A**; don't invent new brand aesthetics or new backend features. Raise actual blocking decisions rather than guessing about consent, checkout, fees, image permissions or privacy.

---

## 21. Final instruction for `/frontend-design`

> **Create a modern, trustworthy, deliberately designed frontend for WiVor Photos: an athlete-first, photography-led sports-event photo marketplace for weekend competitors, photographers and organizers. Use Manrope + Inter, charcoal/off-white surfaces, a restrained `#C64526` orange accent, generous whitespace, realistic sports photographs from authorized WiVor assets, and clear mobile-first search/purchase interactions. Build the master design system first; implement the homepage as the reference; then apply exactly the same system to every other public page in the specified order. Preserve the working Laravel backend, dynamic event data, biometric consent, photo selection, checkout, photographer application and legal content. No generic AI-generated website aesthetic, no unnecessary complexity and no speculative features.**

**END OF MASTER SPECIFICATION.**

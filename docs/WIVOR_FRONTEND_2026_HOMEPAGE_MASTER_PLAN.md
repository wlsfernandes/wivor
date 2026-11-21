# WiVor Photos — 2026 Athlete-First Frontend Redesign

**Execution brief for a local development AI**  
**Project:** https://www.wivorphotos.com/  
**Document status:** Approved creative direction; implementation begins with the public homepage  
**Prepared:** November 20, 2026  
**Assignment:** Apply the **Principal UX/UI, Digital Experience & Frontend Architecture Specialist** role and delegate visual/frontend implementation to the local **`/frontend-design` agent**.

> **NON-NEGOTIABLE SCOPE: FRONTEND ONLY.** Preserve the existing backend, database, APIs, search functionality, routes, user permissions, payments, photo matching, event management, business rules, and production content. Improve the public-facing interface, not the application architecture.

---

## 0. One-minute mission

Transform WiVor Photos from a functional sports photo marketplace into an **emotionally engaging, fast, confident, premium sports experience** that makes a weekend athlete feel: *"That is my race. Those are my moments. I want to find my photos."*

The desired combination is:

- **Nike:** Athletic energy, decisive photographic direction, bold headlines.
- **Unsplash:** Photography is the product; uncluttered presentation.
- **Airbnb:** Search-first usefulness, effortless discovery, trustworthy marketplace patterns.
- **Strava:** The emotional identity of everyday athletes and the sports community.
- **Linear:** Precise components, disciplined whitespace, consistent design system.

These are **principles, not templates**. Do not imitate their branding, trademarks, or visual assets. WiVor must feel distinctive, human-created, and credible—not like a generic AI-generated landing page or a standard SaaS dashboard.

### Main measurable homepage job

An athlete immediately understands **what WiVor is**, sees a recognizable **sports photograph**, and can **search for an event** without scrolling. Secondary audiences (photographers and organizers) clearly see their own entry paths, without competing with the athlete's first-screen experience.

---

## 1. Required AI role and operating method

Act as the **Principal UX/UI, Digital Experience & Frontend Architecture Specialist**, combining:

- UX research, cognitive-load reduction, information architecture, customer journeys;
- principal-level visual design, photography art direction, type, composition and brand identity;
- athlete behavior, marketplace UX, ethical conversion, trust signals;
- accessibility, performance, responsive design, frontend QA;
- senior frontend implementation within the project's existing framework.

### The local frontend-design agent is mandatory

**Explicitly invoke `/frontend-design` for design decisions and frontend execution**, where available in the user's local AI environment. If the agent cannot be invoked, state that clearly in the implementation report and follow its principles yourself; do **not** silently claim to have invoked it.

### Required workflow

1. **Inspect** the codebase, routes used by public views, assets, current CSS and brand tokens.
2. **Document** observed UI and currently used brand color values (exact HEX/RGB and CSS files).
3. **Design** one cohesive visual system before editing multiple sections.
4. **Implement** the homepage first, in small, testable frontend-only edits.
5. **Check** responsive states, existing link/search behavior, performance and accessibility.
6. **Report** exactly what changed, what remains untouched, and what needs future approval.

Do not ask to rebuild the backend. Do not propose a React/Vue/Next migration merely to accomplish a visual redesign.

---

## 2. Hard scope boundaries: what is allowed and forbidden

### Allowed

- Blade or current view-template **presentation markup only**; reuse existing data and action bindings.
- Frontend CSS, existing Tailwind configuration if present, JS for progressive enhancement, animations and accessible navigation.
- Existing public shared partials: header/navigation, footer, hero, visible search forms, event cards **as currently rendered**, and image treatments.
- Locally stored or properly licensed frontend images; optimization of existing images without changing source photo data.
- Frontend-only accessibility, semantics, responsive and interaction fixes.
- Frontend tests/snapshots and screenshots; documentation.

### Forbidden without a separate explicit assignment

- **No** controllers, route definitions, database migrations, SQL/schema changes, seeders, models, policies, auth rules or service classes.
- **No** Stripe/PayPal/checkout rules, cart logic, photo recognition, selfie-processing/biometrics, storage, upload mechanics, CRM, notifications, API contracts or search algorithms.
- **No** changes to form `action`, `method`, input `name`, input meaning, CSRF fields or backend validation contracts unless a verified frontend bug fix is separately approved.
- **No** deleting features, removing legal/privacy links, changing photo watermarks or altering prices.
- **No** invented events, testimonials, review counts, number-of-athletes statistics or fake customer logos.
- **No** unauthorized remote images, hotlinked copyrighted competitor photography or decorative AI-generated athlete photos that impersonate real customer/event coverage.
- **No** new complex frontend framework or heavy animation dependency just to create visual effects.
- **No** direct production deployment unless that is the project's existing explicitly authorized deployment process.

**Safety instruction:** If existing PHP/Blade code mixes data queries with markup, **leave the data logic untouched** and modify only safe presentation code. If a desired design genuinely requires backend/data changes, record it under *Deferred / Not in scope* and implement the best frontend-only alternative.

---

## 3. Verified product facts and journey integrity

Live pages reviewed 2026-11-20:

- `/` — existing homepage: event search by event/city/sport; message about search by bib or optional selfie **after choosing an event**; how-it-works, photographer and organizer entry paths, privacy reassurance.
- `/list-events` — current event discovery; existing search and state/city/sport/date controls.
- `/photographers` — photographer information/application.
- `/organizers` — organizer partnership information.
- `/how-it-works` — athlete journey and search explanation.

**Do not break the distinction between stages:**

1. **Homepage:** find an event (event name/city/sport).
2. **Event/gallery:** locate photographs via the features actually offered for that event (bib number, optional selfie, or browsing).
3. **Gallery/checkout:** preview, select and purchase using existing behavior.

Do not put an unconnected bib-number field or fake selfie-upload CTA in the homepage just for appearance. The homepage primary search must submit **the current working event-search form**, preserving names, action and behavior.

---

## 4. Brand identity: preserve WiVor's CURRENT colors

**Critical: the owner explicitly approves the current WiVor color identity. Preserve it.**

The publicly crawled pages do not expose reliably verifiable live CSS token values in this brief. Therefore **do not guess or replace the primary/secondary HEX values**. The local agent has the project code and must extract the actual values before implementation.

### Mandatory brand-token audit before coding

1. Locate site CSS/SCSS, existing custom properties, CSS framework configuration, logo SVGs and frontend style declarations.
2. Record the exact **primary**, **secondary**, **accent**, **brand-foreground**, and brand-gradient colors that are *actually* in use; identify their source files and whether values are duplicated.
3. Consolidate existing colors into semantic tokens **without altering their RGB/HEX values** (except accessible text/support neutrals, when required).
4. Reuse the existing WiVor logo and its original proportions; no rebranding or replacement logo.
5. Test accessible text contrasts and use non-brand neutral backgrounds/darker overlays as needed rather than modifying brand colors.

Suggested token structure; replace `EXISTING_...` placeholders with **verified current WiVor values**:

```css
:root {
  --wivor-brand-primary: EXISTING_PRIMARY_COLOR;
  --wivor-brand-secondary: EXISTING_SECONDARY_COLOR;
  --wivor-brand-accent: EXISTING_ACCENT_COLOR_IF_ANY;
  --wivor-brand-on-primary: VERIFIED_CONTRASTING_TEXT_COLOR;

  /* Supporting neutrals are design proposals, NOT replacement brand colors. */
  --surface-page: #f7f8fa;
  --surface-white: #ffffff;
  --surface-dark: #10151b;
  --text-primary: #131a21;
  --text-secondary: #56616c;
  --border-subtle: #e5e9ed;
}
```

If an existing brand color is unsuitable for small text on white, continue using it for large areas/buttons and select sufficiently contrasting text/neutral treatment; document the adjustment.

**Desired emotion:** athletic, confident, energetic, cinematic, premium, warm, real.  
**Avoid:** flat corporate SaaS, generic gradient blobs, over-branded neon, giant rounded pills everywhere, sterile fintech layouts, newspaper/editorial serif typography, excessive black backgrounds.

---

## 5. Exact type system: bold sport energy, clean modern UI

### Chosen font pairing

**Display and athletic headlines: `Barlow Condensed`** (600, 700, 800).  
**Body, menus, labels and controls: `Manrope`** (400, 500, 600, 700).

This is an intentional **sports display + precise contemporary UI** combination—not a newspaper or generic system-serif style. Self-host authorized WOFF2 files if possible; use existing approved font-loading infrastructure, and fallback gracefully:

```css
--font-display: 'Barlow Condensed', 'Arial Narrow', 'Helvetica Neue', sans-serif;
--font-ui: 'Manrope', Inter, system-ui, -apple-system, 'Segoe UI', sans-serif;
```

If a font already has an enforceable licensing/brand restriction, preserve the restriction and document the closest suitable alternative. Use `font-display: swap` with metrics-aware fallbacks if local infrastructure supports them. Do not bundle unnecessary weights or variants.

### Typography specification

| Token | Desktop | Mobile | Weight | Line height | Letter spacing |
|---|---:|---:|---:|---:|---:|
| Hero display | `clamp(4.5rem, 7.7vw, 7rem)` (72–112px) | `clamp(3.4rem, 12vw, 4.65rem)` (54–74px) | Barlow Condensed 800 | 0.89–0.94 | -0.025em |
| Section H2 | `clamp(2.9rem, 4.3vw, 4.75rem)` | `clamp(2.35rem, 8.5vw, 3.2rem)` | Barlow Condensed 700/800 | 0.98–1.03 | -0.015em |
| Card H3 | 30–34px | 25–29px | Barlow Condensed 700 | 1.05 | -0.01em |
| Lead paragraph | 20px | 17px | Manrope 400/500 | 1.55 | normal |
| Regular body | 16px | 16px | Manrope 400 | 1.6 | normal |
| Small/metadata | 13–14px | 13–14px | Manrope 500/600 | 1.4 | 0.005em |
| Navigation | 14px | 15px | Manrope 600/700 | 1.2 | normal |
| Button | 14–15px | 15px | Manrope 700 | 1.1 | 0.005em |
| Eyebrow label | 12–13px | 12px | Manrope 700 | 1.3 | 0.09–0.11em |

### Type rules

- Use display font only for **high-impact headlines and selected short card headings**. UI/body must remain clean and readable.
- H1 sentence case / tasteful stacked lines. Avoid making every paragraph all caps.
- Eyebrows may be uppercase but limited to one short line.
- Constrain body paragraphs to **58–68 characters** / approximately 38–42rem max-width, not endless full-width text.
- Avoid underlined fake links, ultra-light text, type placed over busy faces or bib numbers, and center-aligning large blocks of explanatory copy.
- Use real semantic heading order and one H1 per page.
- Font sizes are **design targets**. Preserve legibility under browser text zoom to 200% and mobile viewport reflow.

---

## 6. Precise spacing, geometry and layout system

### Core spacing scale (4px basis)

| Token | Value | Typical usage |
|---|---:|---|
| `space-1` | 4px | small icon refinements |
| `space-2` | 8px | icon/text tight gap |
| `space-3` | 12px | field grouping |
| `space-4` | 16px | compact UI, mobile gutters |
| `space-5` | 20px | body/control rhythm |
| `space-6` | 24px | card padding and grid gap |
| `space-8` | 32px | mobile content gap |
| `space-10` | 40px | intermediate composition gap |
| `space-12` | 48px | medium components |
| `space-16` | 64px | tablet section spacing |
| `space-20` | 80px | desktop section spacing |
| `space-24` | 96px | spacious editorial section |
| `space-28` | 112px | hero/large section separation |

### Responsive rules

- **Content max width:** 1280px (large desktop); **reading column:** 680px max.
- **Desktop container:** `width: min(100% - 80px, 1280px)` at >= 1200px.
- **Tablet container:** left/right 32px at 768–1199px.
- **Mobile container:** left/right 20px at 375–767px; 16px where 320–374px requires it.
- **Breakpoints:** 480px, 768px, 1024px, 1280px; respond to actual content needs.
- **Desktop grid:** 12 columns, 24px column gaps.
- **Tablet:** 8 columns, 20px gaps; **mobile:** 4 columns, 12–16px gaps.
- **Desktop section padding:** 96px top and bottom for main sections, varying to 112–120px for hero/editorial transitions.
- **Tablet:** 72px vertical; **mobile:** 56–64px vertical.
- **Short section heading → description:** 12–16px; heading block → content grid: 36–48px desktop, 24px mobile.
- **Card spacing:** 24px internal desktop; 20px mobile.
- **Avoid uniform gap everywhere:** intentionally alternate tightly related elements with open section boundaries.

### Shapes, shadows and borders

- Buttons: **10px** radius (not giant pills); primary 52–56px tall desktop / at least 50–52px mobile.
- Search bar: **12–14px** radius; visually confident and obvious, not a floating gigantic capsule.
- Event cards: **12–14px** radius; photos often edge-to-edge within cards.
- Editorial photos: **8–12px** radius or intentionally square edges for dramatic full-bleed compositions.
- Borders: 1px quiet cool neutral; avoid thick outlines across every card.
- Shadows: use sparingly, e.g. `0 12px 32px rgba(9, 20, 30, 0.10)` for an elevated search panel, rather than everywhere.
- No excessive glassmorphism, floating gradient or randomly tilted cards.

---

## 7. Motion system: premium and restrained

Motion should feel like **forward athletic momentum**, not a showreel.

| Interaction | Duration | Easing / motion | Limit |
|---|---:|---|---|
| Button hover | 160–200ms | color/translateY(-1px) | no bounce |
| Card hover | 220–260ms | media scale 1 → 1.025, subtle elevation | respect crop |
| Navigation underline/active | 160–200ms | ease-out | accessible state |
| Input focus | 150–180ms | border/focus-ring transition | never hide focus |
| Hero headline introduction | 500–650ms | opacity + translateY(12–18px) | once, no long wait |
| Hero secondary text | 500–650ms | same, 80–110ms stagger | once |
| Hero search | 450–550ms | opacity + translateY(8px), 120ms stagger | **always usable** |
| Below-fold section reveal | 350–500ms | opacity + translateY(12–16px) | optional progressive enhancement |
| Mobile navigation drawer | 220–260ms | translateX/opacity | ESC closes / focus managed |

- **Preferred easing:** `cubic-bezier(0.22, 1, 0.36, 1)` for entrance; `ease-out` for controls.
- Never endlessly animate the H1, search controls, cards, or background.
- **Do not autoplay sound.** Avoid autoplay hero video; a powerful optimized photo is preferred for first iteration.
- No parallax on mobile. Avoid scroll-jacking, horizontal page scroll, heavy third-party animation bundles and staggered elements that stay hidden if JS fails.
- `prefers-reduced-motion: reduce` must disable nonessential transforms/reveals and retain visible content.
- Animate only `transform`/`opacity` where practical, not layout width/height or massive expensive filters.

Example baseline:

```css
:root { --ease-out-expo: cubic-bezier(.22, 1, .36, 1); }
.button, .event-card { transition: transform .22s var(--ease-out-expo), box-shadow .22s ease, background-color .18s ease; }
.button:hover { transform: translateY(-1px); }
.event-card:hover .event-card__image { transform: scale(1.025); }
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; transition-duration: .01ms !important; }
}
```

The snippet is a starting point only. Do not impose broad `*` transitions outside reduced-motion safeguards.

---

## 8. Homepage: precise section-by-section implementation

### Visual concept: **THE MOMENT IS YOURS**

**Creative story:** The athlete worked for that finish line. WiVor brings the moment back. The homepage is visual, confident and uplifting, with genuine sports action and a clear path to photos.

### Section 1 — Global header / navigation

**Desktop geometry**

- Preferred header height **76–80px**; compact enough to leave hero room.
- Existing WiVor logo left, vertically centered, max visible height approximately 36–40px without distortion.
- Navigation right or center: `How It Works`, `Athletes`, `Photographers`, `Organizers`, `FAQ`; preserve actual working destinations and accessible behavior.
- `Log in` as low-emphasis secondary link; `Find My Photos` as branded primary button (height 44–46px, radius 9px).
- White or current site-neutral header with high-contrast text. Avoid a transparent hero-overlay header if contrast and consistency degrade.
- Sticky header optional **only if tested**; if sticky, use a minimal surface/shadow after scrolling, no flashy transitions.

**Mobile geometry**

- Height **64–68px**, logo left, visible search/Find Photos action as space permits, menu toggle right.
- Mobile menu must open/close reliably; no body scroll while modal drawer is open; focus trapping/restoration as appropriate, `aria-expanded`, ESC handling.
- Do not add multiple equivalent prominent CTAs that crowd the first row.

### Section 2 — Hero: the signature first impression (highest priority)

**Desired desktop composition:** A visually powerful **full-bleed / near-full-bleed professional race photograph**, with the athlete visible on the **right 50–60%**, readable content on the **left 40–50%**, and a carefully graded dark neutral overlay on the text side. The existing WiVor colors appear in the prominent CTAs and details, not by recoloring the imagery.

**Photography:** Prioritize the repository's existing image of a **runner with race bib approaching cheering spectators** or a higher-quality authorized race image if available. The picture should show human emotion, movement, effort or accomplishment. Ideally show recognizable weekend athletes, not an impossibly polished commercial super-athlete. **The eye/face/bib should remain visible**, never buried under hero text or awkward crops.

**Desktop sizing**

- Hero target total height **min 680px**, generally 680–760px at large desktops; avoid rigid height if text/search wraps.
- First view should include H1, supporting copy, and the working event search at ~1366×768 where reasonably possible.
- Left content max-width **660px**; left inner padding **56–72px** relative to hero frame; right image remains dominant.
- If hero is full viewport width, use an inner `max-width: 1280px` container for text to align with downstream sections.
- Background position may need art-directed breakpoints; target roughly **right 35–45%** depending on subject placement.
- Add a subtle left-to-right **dark neutral gradient**, e.g. dark rgba on left fading to transparent by ~70%; do not use bright gradients that fight the original brand.
- Contrast must pass on text and controls over the image.

**Proposed hero content (editable copy, not fabricated functionality)**

Eyebrow: `EVERY RACE HAS A STORY`  
H1:

> **YOUR RACE.**  
> **YOUR MOMENT.**  
> **YOUR PHOTOS.**

Text:

> Find your event, discover your race photos, and relive the moments worth keeping. Search by bib number or optional selfie once you're inside the event.

Search label: `Find your event`  
Existing field placeholder: `Search by event, city, or sport`  
Primary submit CTA: `Find My Photos` (or retain the exact form's existing `Search Events` label if that is clearest when evaluated).  
Optional single secondary text CTA: `See how it works` → existing route.

**Search treatment**

- Visible search block in the hero with a clear label; **never** a decorative fake search field.
- Desktop width approx **560–640px**; input and submit button on one row if this matches the existing form contract.
- Search outer container neutral white, border radius 12px, padding 6–8px, field height 54–56px, button height 52–56px.
- Prominent dark readable input text and consistent focus treatment. Input label remains accessible even if a visible label is also displayed.
- Preserve existing input `name`, action, method, suggestions, error handling, and query persistence.
- Do not invent a homepage bib search; it belongs inside a selected event.

**Mobile hero**

- Design mobile **from scratch**, not a shrunk desktop screenshot.
- Prefer image-led composition: photograph with subject positioned upper/right and a controlled dark overlay beneath/behind text; alternatively a content-first intro plus image panel if the actual asset cannot crop accessibly.
- Title **54–74px** condensed across 2–3 purposeful lines, not 6 accidental wraps.
- Side gutters **20px**, intro text 16–17px, label 14px, search input/button each full-width **52–56px** tall, gap 10–12px.
- No fixed viewport height that hides the search under browser chrome; use padding + sensible min-height, test at **375×667** and **390×844**.
- Keep important sports subject visible in mobile art direction; use `<picture>`/`object-position` responsive strategy if possible.

**Acceptance gate:** One glance communicates *sports photography + search your event + next action*. The hero must inspire an athlete **and work as an actual event search**.

### Section 3 — How WiVor works (functional clarity)

**Purpose:** Remove doubt without creating a boring corporate features grid.

- Section neutral/white background with top/bottom **96px desktop, 60px mobile**.
- Left/top eyebrow `IT'S SIMPLE`, headline `FROM FINISH LINE TO FAVORITE PHOTO.`
- One concise explanation: `Find your event. Find yourself. Choose the memories you want to keep.`
- Three steps, preserving the existing actual capabilities:
  1. `FIND YOUR EVENT` — Search by race, location, or sport.
  2. `FIND YOURSELF` — Within the event, search by bib number or optional selfie, or browse photos where available.
  3. `KEEP YOUR MOMENTS` — Preview, choose, purchase, and download via the existing flow.
- Desktop 3-column grid, 24px gaps; mobile one column with generous 24px space between steps.
- Big typographic step labels `01 / 02 / 03` in brand accent/neutral; a **fine connecting line** is acceptable on desktop only.
- Use no more than one simple relevant line icon per item. Avoid arbitrary circles surrounding every icon.
- `See the full process` link goes to existing `/how-it-works`.

### Section 4 — Events / discovery (only real existing data)

**Purpose:** Offer real proof and a quick route to discoverability.

- Heading: `YOUR NEXT GREAT MOMENT STARTS HERE.`
- Supporting line: `Explore events and find the photos that tell your story.`
- Action: `Browse All Events` → existing `/list-events`.
- If the homepage already receives event items from its current frontend data contract, style up to **3** real cards; do not add queries or controllers.
- If homepage has **no event data**, do **not** fake three cards or hardcode specific events. Instead use a **beautiful editorial CTA banner** to the existing event list, or keep this section concise until data is made available in a separately approved task.
- Event card image: aspect ratio **4:3 or 3:2**, consistent subject positioning; no invented event photography association.
- Event card information when already available: real title, sport, event date/location, actual availability state, working event link.
- Gallery imagery should be dominant; text overlays must not cover primary subjects. No fake 'popular' or 'sold out' labels.

### Section 5 — Athletic storytelling / emotional photography moment

**Purpose:** Make the site feel alive rather than informationally flat.

- Create a **wide editorial photo composition** using existing authorized race imagery (one large image, or an intentional 2-image asymmetrical layout).
- Copy example: `THE RACE ENDS. THE FEELING STAYS.`
- Short supporting text: `The effort, the celebration, the people cheering you on—those moments deserve more than a passing memory.`
- This should **not** be another four white feature cards. Use visual rhythm: dark photo → bright content, edge-to-edge image → controlled typography.
- May use subtle scroll reveal; imagery itself should carry emotion.
- A gentle text link to find events is sufficient if the primary search has already appeared clearly.

### Section 6 — Photographer invitation (secondary audience)

- Strong portrait/action image of a photographer working at an event, ideally from the repository's existing authorized images.
- Desktop split **50/50**, image on one side, text on the other; reverse gracefully on mobile.
- Eyebrow: `FOR PHOTOGRAPHERS`.
- Headline: `CAPTURE THE ACTION. REACH THE ATHLETES.`
- Accurate description: `Upload your event photos, help athletes discover your work, and sell through WiVor.`
- Existing supported detail only: no signup fee; platform commission on sale, if still accurate and approved by existing content.
- CTA `Explore Photographer Opportunities` → `/photographers`.
- Do not imply guaranteed income, immediate acceptance or an unsupported photographer feature.

### Section 7 — Organizer invitation (tertiary audience)

- Visually connected but distinct from photographers: understated editorial panel, **not** another identical 50/50 section unless that gives a better rhythm.
- Eyebrow: `FOR EVENT ORGANIZERS`.
- Headline: `GIVE ATHLETES MORE TO REMEMBER.`
- Accurate message from current site: WiVor connects event photographers and athletes; approved organizer benefits only.
- CTA `Partner With WiVor` → `/organizers`.
- Never invent statistics, partner names, zero-cost promises beyond already-approved content, or operational commitments.

### Section 8 — Trust, purchase clarity and privacy

**Purpose:** Reduce anxiety at the moment of buying photos.

- Compact section with 3 clearly stated facts, all verified against current content:
  - `Preview before you buy`.
  - `Choose the photos you love`.
  - `Selfie search is optional and requires consent`.
- Include a clear route to the **existing Biometric Notice** and all relevant privacy information; never minimize facial-search consent language.
- Use lean text and minimal icons, not three oversized generic cards.
- If secure payments are mentioned, do not display fake security badges or certification marks.

### Section 9 — Final athletic CTA

- Contrasting branded/dark section, vertical padding **88–112px desktop, 64px mobile**.
- Short headline: `READY TO RELIVE YOUR RACE?`
- Copy: `Your favorite moments may be waiting. Start by finding your event.`
- Primary CTA `Find My Photos` → existing `/list-events` or existing supported home search focus behavior.
- Clear urgency without fabricated scarcity or countdown timers.

### Section 10 — Footer

- Preserve all necessary existing destinations, legal/privacy links, company details and consent notices.
- Better typography, grid, hierarchy, section spacing and responsive stacking.
- Desktop approx 4 logical columns if content supports them; mobile thoughtfully stacked groups.
- No broken placeholders, copied icons from competitors, dark-on-dark links or huge empty gaps.

---

## 9. Search UX and conversion details

The **homepage search** is a core product UI, not merely a marketing decoration.

- Always retain a visible label and keyboard-submit behavior.
- Enter key must trigger the working current search; the button must perform the same action.
- Empty queries: preserve existing intended default/search-list behavior; do not invent new validation restrictions.
- Long event names, accents, spaces, special characters and city/sport terms must continue to work.
- Preserve query strings, escaping, and CSRF requirements applicable to the existing form.
- Search icon optional, not a substitute for a text label.
- No fake autocomplete; only display suggestions if supported by the existing UI/backend.
- For loading states, use existing behavior. Do not trap the input with a spinner that never resolves.
- Use discoverable button labels; users should not have to infer search from a small magnifying-glass icon alone.
- Event-list and event-detail functionality must remain unchanged.

### CTA hierarchy

1. **Primary, athlete:** `Find My Photos` / `Find Your Event` — branded filled button, visible in header/hero/final CTA.
2. **Secondary, process:** `How It Works` — text link or outlined neutral button.
3. **Photographer:** own CTA in own section.
4. **Organizer:** own CTA in own section.

The homepage should never offer five equally prominent calls to action above the fold.

---

## 10. Photography and asset art direction

Image quality is part of WiVor's perceived product quality.

### Photo selection criteria

- Authentic sporting events, **real participants**, visible movement and emotion.
- Strong highlights/shadows, clear focal point, energetic crop.
- Weekend athletes and race communities—not only elite professionals.
- Multiple ages/backgrounds/sports represented *when the approved asset library supports it*.
- Avoid synthetic/glossy photography, generic smiling-office-team stock images or repeated photo assets across every section.
- Use images that WiVor owns or is explicitly permitted to publish. Do not source competitor website assets.

### Technical media rules

- Hero image: WebP/AVIF where practical with JPEG fallback; preserve source fidelity and image licensing.
- Prefer `<picture>` + responsive `srcset`/`sizes` and explicit intrinsic `width`/`height` or CSS `aspect-ratio` to avoid layout shift.
- **Hero/LCP image is eager-loaded** and, if confirmed to be the LCP resource, considered for `fetchpriority="high"`. **Never lazy-load the hero.**
- Lazy-load noncritical images below the fold; avoid loading the whole photo library on the homepage.
- Keep meaningful descriptive alt text; decorative images can have empty alt when appropriate.
- Maintain photographer attribution if required by existing licensing.
- Do not overlay type across a face/bib when the crop becomes hard to understand.
- Keep all image CDN/S3 links/back-end asset resolution intact; do not migrate media storage.

---

## 11. Accessibility, responsive and quality gates

### Accessibility acceptance criteria

Target **WCAG 2.2 AA** in the changed UI:

- Semantic `header`, `nav`, `main`, `section`, `footer` and sensible heading hierarchy.
- All interactive controls accessible by keyboard, with visible high-contrast focus.
- Color contrast at least **4.5:1 normal text**, **3:1 large text** where applicable.
- Touch targets **at least 44×44 CSS px as a preferred design goal** (exceeds the general WCAG AA minimum in many cases).
- Form labels, clear button names, usable errors, visible loading/empty states where present.
- Desktop and mobile navigation keyboard/assistive-technology friendly.
- No content hidden on reduced motion or when JS is unavailable.
- No horizontal overflow at 320px, 375px, 390px, 768px, 1024px, 1440px and 1920px.
- Test 200% browser text zoom/reflow, not only normal desktop screenshots.

### Performance targets

- LCP **≤2.5s** on the relevant field population where achievable.
- INP **≤200ms**.
- CLS **≤0.1**.
- Avoid heavy hero video/background animation, large icon kits, unnecessary JS, and unoptimized font/image requests.
- Do not declare success from one synthetic Lighthouse run. Capture before/after measurements and state whether field data exists.

### Visual regression checks

- Header height/scroll behavior, nav focus/hover/menu.
- Hero text overlay and image crop at each breakpoint.
- Search field keyboard submission and existing destination.
- Every existing public link and marketing section.
- Buttons, hover/focus styles, reduced-motion behavior.
- No text collisions, orphans, unexpected card heights or CLS from image loads.
- Footer and legal/privacy destinations.

---

## 12. Implementation order and delivery gates

### Phase A — Audit and protect (required before editing)

- Identify actual codebase framework/assets and the homepage template/partials.
- Record screenshots of current desktop and mobile homepage using an available local browser/test harness.
- Read the **actual brand color values** from local styles and record their original source.
- List current homepage search form contracts: route/action, method, input names, query persistence, any JS.
- Note existing logos/hero imagery/licensing, current fonts, and every public homepage link.
- Create an appropriate Git branch or working checkpoint; never force-overwrite uncommitted user work.

### Phase B — Build design foundation

- Add semantic brand/neutral/spacing/type/radius/easing tokens to existing frontend styling organization.
- Integrate specified fonts efficiently and use responsive type and spacing.
- Create shared UI primitives only where the existing project supports them naturally.
- Preserve all current brand colors.

### Phase C — Homepage priority release

**P0 (must have):**

1. Global header + mobile navigation polish.
2. Signature athlete-first photo hero.
3. Functional event search in the hero.
4. Precise type, spacing, responsive image handling and mobile first fold.
5. Preserved legal/privacy and existing navigation behaviors.

**P1 (next in same frontend rollout):**

6. Three-step how-it-works.
7. Event-discovery section using **only available real data**, otherwise editorial CTA.
8. Athletic image/storytelling moment.
9. Photographer and organizer sections with proper hierarchy.
10. Trust/privacy, closing CTA and footer refinement.

**P2 (optional after validations):**

11. Fine-grained reveal animations and subtle micro-interactions.
12. Further photo art direction at mobile crop breakpoints.
13. Frontend consistency fixes shared with other **public** pages, but do not redesign them without a separate page brief.

### Phase D — QA, proof and report

- Test at listed viewports; capture before/after screenshots at desktop and mobile.
- Exercise event search, Enter submission, existing nav links, photographer/organizer links, legal/privacy routes.
- Report Lighthouse/lab performance with conditions; do not invent metrics.
- Provide changed frontend file list, design token table including **actual color values**, tests performed, and deferred requests.
- Verify `git diff` includes **no** routes, controllers, models, migrations, payment code, search logic, upload or recognition services.

---

## 13. Acceptance checklist: definition of done

- [ ] The page immediately feels like a **premium, energetic, authentic SPORTS PHOTOGRAPHY** brand.
- [ ] Existing WiVor primary/secondary brand colors and logo are **unchanged**.
- [ ] Fonts are deliberate and modern: **Barlow Condensed + Manrope** or a documented licensed equivalent.
- [ ] Hero is photographic, memorable and athlete-first—**not** generic cards and gradients.
- [ ] Homepage's real event search remains functional and is visible above the fold on tested desktop sizes.
- [ ] No false promise that bib/selfie photo matching happens on the homepage.
- [ ] Mobile hero typography, crop, CTA and spacing are intentionally designed.
- [ ] Photographers/organizers have clear routes **without distracting the athlete**.
- [ ] The UI works with keyboard, reduced motion and narrow screens.
- [ ] All copy/claims, events and photography are truthful and authorized.
- [ ] Existing checkout, auth, photo search and backend business behavior have not changed.
- [ ] No route/controller/model/migration/database/API/backend change was made.
- [ ] No unnecessary JS framework or large animation package was introduced.
- [ ] Images are responsive and hero loading is performance-aware.
- [ ] Screenshot comparisons and QA notes exist.
- [ ] Implementation report lists concrete changes and any unresolved limitations.

---

## 14. Required developer handoff output

When implementation is complete, produce:

1. **A concise implementation report** at `docs/WIVOR_HOMEPAGE_FRONTEND_IMPLEMENTATION_REPORT.md` (create `docs/` only if appropriate in this repository), summarizing frontend changed files, actual colors preserved, fonts used, before/after screenshots, and tests.
2. **A design token reference** at `docs/WIVOR_FRONTEND_DESIGN_SYSTEM.md` containing exact verified brand HEX values and the implemented tokens, type scale, spacing, breakpoints, interaction details.
3. **A strictly frontend-only change set** with no deployment unless authorized.
4. **A deferred-scope list** for any idea that would require backend/API/database work, with **no implementation** of those items.

If the repository has a different established documentation convention, follow it and identify the final paths.

### Final instruction to the local AI

> **Do not simply restyle the current page section by section. Compose a coherent, visually striking athlete-first experience. Use photography, bold display type, controlled negative space, memorable content rhythm, real search functionality and careful mobile art direction. Preserve WiVor's current brand palette, preserve all existing product functionality, and do not touch the backend. Begin with the homepage. Invoke `/frontend-design` for the frontend work.**

---

## 15. Research references and site verification

Product pages checked November 20, 2026:

- WiVor homepage: https://www.wivorphotos.com/
- WiVor event list: https://wivorphotos.com/list-events
- WiVor how it works: https://wivorphotos.com/how-it-works
- WiVor photographers: https://www.wivorphotos.com/photographers
- WiVor organizers: https://wivorphotos.com/organizers

Technical reference standards:

- W3C WCAG 2.2: https://www.w3.org/TR/WCAG22/
- Google web.dev Core Web Vitals: https://web.dev/articles/vitals
- Google web.dev LCP optimization: https://web.dev/articles/optimize-lcp/
- web.dev responsive images: https://web.dev/learn/design/responsive-images
- W3C reduced-motion technique: https://www.w3.org/WAI/WCAG22/Techniques/css/C39

**Verification caveat:** Homepage copy/structure and existing routes were checked on the public site, but the project's private frontend styles and exact brand HEX values were not available in this research environment. The local agent must inspect the real repository and preserve those actual values; it must not treat placeholder color tokens as real brand definitions.

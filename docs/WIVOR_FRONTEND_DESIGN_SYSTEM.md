# WiVor frontend design system: homepage 2026 tokens

This file adds to `docs/frontend/00-frontend-guide.md`. The guide's tokens still apply to every public page. The tokens below are new and apply to the homepage only (everything inside `.wv-home`).

Source: `public/assets/css/wivor-brand.css`. The `:root` block at the top holds the brand tokens. The "Homepage" block at the end holds the homepage tokens and components.

## Brand colors (verified, unchanged)

| Token | Value | Use |
|---|---|---|
| `--wivor-accent` | `#ff3d00` | Primary buttons, last hero line, closing band |
| `--wivor-accent-hover` | `#c64526` | Button hover, audience labels on white (4.84:1) |
| `--wivor-accent-subtle` | `#fff0ea` | Upcoming badge background |
| `--wivor-ink` | `#151b22` | Headlines, dark sections, footer |
| `--wivor-ink-soft` | `#28323d` | Ink button hover |
| `--wivor-text` | `#34404c` | Body text |
| `--wivor-muted` | `#596675` | Secondary text, dashed step line |
| `--wivor-bg` | `#f7f8f6` | Page background |
| `--wivor-surface` | `#ffffff` | White sections, search panel |
| `--wivor-surface-subtle` | `#f1f3f3` | Light sections, organizer panel |
| `--wivor-border` | `#e1e6e7` | Input border in the hero |
| `--wivor-on-dark` / `--wivor-on-dark-muted` | `#f7f8fa` / `#dfe4e8` | Text on ink |
| `--wivor-focus` | `#1174b8` | Keyboard focus ring |

Logos: `public/assets/images/logo/wivor.png` (header), `wivor_white.png` (footer). Both are unchanged.

### Contrast notes

- White on `#ff3d00` is 3.55:1. That passes only for large text (at least 18.66px bold). On the homepage, every orange button uses Barlow Condensed 700 at 20px, so it passes. The shared header button ("Find My Photos", 15px) still fails. See the implementation report.
- Ink on `#ff3d00` is 4.85:1, so the closing band uses ink text. The focus ring on that band is ink, because blue is hard to see on orange.

## Homepage tokens (new)

| Token | Value |
|---|---|
| `--wivor-font-display` | `'Barlow Condensed', 'Arial Narrow', 'Helvetica Neue', sans-serif` |
| `--wivor-font-ui` | `'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif` |
| `--wivor-ease-out-expo` | `cubic-bezier(0.22, 1, 0.36, 1)` |
| `--wivor-overlay-strong` / `-medium` / `-soft` | ink at 0.94 / 0.80 / 0.28 alpha (hero gradient) |

Fonts: Barlow Condensed 700 and 800 load from Google Fonts on the homepage only (`@section('styles')` in `home/index.blade.php`). Manrope 500–800 is already loaded site-wide.

## Type scale (homepage)

| Role | Mobile | Desktop (≥1024px) | Face, weight, line height |
|---|---|---|---|
| Hero H1 | `clamp(2.5rem, 12.5vw, 4.65rem)` | `clamp(4.5rem, 7.7vw, 7rem)` | Barlow Condensed 800, 0.9, uppercase via CSS |
| Section H2 (`.wv-home-h2`) | `clamp(2.35rem, 8.5vw, 3.2rem)` | `clamp(2.9rem, 4.3vw, 4.75rem)` | Barlow Condensed 800, 1.0, uppercase via CSS |
| H3 (`.wv-home-h3`) | 27px | 32px | Barlow Condensed 700, 1.05, sentence case |
| Lead (`.wv-home-lead`) | 17px | 20px | Manrope 500, 1.55 |
| Body (`.wv-home-body`) | 16px | 16px | Manrope 500, 1.6 |
| Audience label | 14px | 14px | Manrope 700, sentence case |
| Primary CTA (`.wv-home-cta`) | 20px | 20px | Barlow Condensed 700 |

The HTML keeps headlines in sentence case. `text-transform: uppercase` is applied to H1 and H2 only.

## Spacing and layout

- Container: the existing `.wv-container` (max 1240px; side padding 16, 24 and 32px at 0, 640 and 1024px). The homepage reuses it so it aligns with the header.
- Section padding (`.wv-home-section`): 60px on mobile, 72px from 768px, 96px from 1024px. The closing band uses 64, 88 and 112px.
- Breakpoints used: 576, 768, 1024 and 1601px.
- Radii: buttons 10px (`--wivor-radius`); search panel, cards and photos 14px (`--wivor-radius-card`). Bib plates use 6px.
- Shadow: only the hero search panel has one (`0 12px 32px rgba(9, 20, 30, 0.10)`).

## Components

- **Hero (`.wv-home-hero`):** On desktop, the photo fills the hero. It is anchored left so the runner lands right of the copy, and a left-to-right ink gradient sits behind the text. The photo frame stops at 1600px, because the source file is only 1200px wide. On mobile and tablet, the photo sits on top and the copy overlaps its faded bottom edge by 48px.
- **Search panel:** the shared `components/event-search` partial, restyled inside the hero only. Its form contract is unchanged.
- **Bib steps (`.wv-home-steps`):** the counter is drawn as a race bib with four pin holes. On desktop, a dashed line links the bibs. Use this only for real sequences.
- **Audience label (`.wv-home-audience`):** names who a section is for. It replaces generic eyebrows.
- **Text link (`.wv-home-text-link`):** ink text with a 2px orange underline, and a 44px touch height.

## Motion

- One hero entrance: children of `.wv-hero__intro` rise 14px and fade in over 600ms with `--wivor-ease-out-expo`, staggered at 0, 90, 200 and 280ms. The search uses 500ms.
- Button hover: `translateY(-1px)` over 180ms.
- `prefers-reduced-motion: reduce` removes the entrance, the hover transform and the transitions.
- There are no scroll reveals and no looping animation.

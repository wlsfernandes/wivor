# Homepage content and conversion brief

## Primary goal

Within five seconds, a first-time athlete should understand that WiVor helps them find and buy photos from their event, and should see how to begin. A photographer should find a clear, credible application path without interrupting the athlete journey.

## Recommended section order

### 1. Hero: category, emotion, action

Keep the existing finish-line photograph if its use is authorized and the crop works on mobile. Preserve the working event search in the hero.

**Recommended H1:** “Find your race photos. Relive the moment.”

**Supporting copy direction:** “Search your event, find yourself by bib number [or optional selfie when enabled], and preview the photos before you buy.” Keep this conditional with the current feature flag; do not promise selfie search when disabled.

**Primary action:** the current `Search Events` form. Keep its real route and `search` parameter. The photographer link may remain secondary, but should not draw equal attention to the search.

The current “Your race. Your moment.” is emotionally good but does not identify the product by itself. It can remain as a small supporting line if the layout benefits from it.

### 2. Genuine photo proof

Show a compact selection of events with **actually available** galleries if the existing application can provide accurate event data and permitted cover photos. Each item should link to the real event and state only verifiable name, date, location, and photo availability.

The current homepage controller passes no event data. Treat this as a separate, small data-integration step and review the query and gallery rules before implementation. Until then, use a direct link to the existing event directory. Do not hardcode sample events, photo counts, or invented gallery previews.

### 3. Athlete value and simple process

Use one short line about the moment worth keeping, then retain the existing three-step path: find event, find yourself, choose photos. Keep the practical instructions concise; the dedicated How It Works page can hold details.

### 4. Photographer opportunity

Give photographers a section that looks distinct from the athlete steps and uses the existing photographer image only if authorized.

**Suggested heading:** “You capture the race. Help athletes find your work.”

Explain the verified workflow: upload event photos, make them discoverable to athletes, and sell through WiVor. If discussing fees, say accurately that there is no sign-up fee and WiVor takes a commission on sales, after checking current terms. Link `Apply as a Photographer` to the working application section.

### 5. Organizer path

Keep a concise section or band: professional photos improve the participant experience; WiVor works with photographers to cover events. Link to the existing organizer page. Avoid promises about coverage, workload, or economics that the current service cannot guarantee.

### 6. Purchase reassurance and final action

Keep the useful current facts: preview first, buy only selected photos, and an accurate privacy or biometric notice. Avoid repeating these in several card grids. End with one clear `Find My Photos` action to event discovery.

## Visual and mobile direction

- Keep the existing design tokens and public components in `public/assets/css/wivor-brand.css`.
- Let the race image carry most of the energy; use orange for the primary CTA and a few sharp accents.
- On a 390px phone, show the category, value, and working search within the first useful viewport. The photo follows immediately.
- Avoid a carousel, fake proof, repeated icon cards, decorative animation, or large orange background blocks.
- Keep photographer and organizer sections visually different while sharing the same brand system.

## Success checks

- A new athlete can answer: “What is WiVor, and what do I do first?”
- The hero search reaches the event directory with the entered term.
- Every event/photo shown as proof is real, available, and permitted for reuse.
- Photographer CTA reaches the existing application; organizer CTA reaches the existing partnership path.
- The page works at 360px, 390px, tablet, and desktop without overflow or hidden actions.
- No claim changes the meaning of fees, privacy, photo availability, or purchasing.

# Instructions for the local AI: homepage marketing iteration

## Task

Improve the homepage's perceived value for athletes and photographers using `01-positioning.md` and `02-homepage-brief.md`. Preserve the approved visual system in `docs/frontend/00-frontend-guide.md`. Make the smallest safe changes required to deliver the new message hierarchy and section order.

## Read before editing

1. Root `AGENTS.md` and `docs/frontend/00-frontend-guide.md`.
2. `resources/views/frontend/v1/home/index.blade.php`.
3. `resources/views/frontend/v1/components/event-search.blade.php`, public header/footer, and `public/assets/css/wivor-brand.css`.
4. The homepage route and controller, plus photographer and organizer destinations.
5. Current feature flags and terms before writing about selfie search, fees, or earnings.

The frontend guide calls for the `/frontend-design` agent before code changes. Invoke it if available. If unavailable, state that plainly and follow the guide directly. Do not claim an agent was used unless it ran.

## Implementation sequence

1. **Audit:** note current page behavior, the assets that may be reused, and any claims that need verification. Capture a mobile and desktop baseline if browser tooling is available.
2. **Copy and hierarchy:** update the hero and rearrange existing content so athlete value and search lead, photographer value is distinct, and organizer content follows. Reuse existing Blade/Bootstrap patterns and public components.
3. **Visual refinement:** adjust only homepage-scoped styles needed for clearer emphasis, spacing, image crop, and mobile composition. Keep canonical tokens and avoid changes to admin pages.
4. **Real gallery proof:** add it only if genuine live events and permitted imagery can be retrieved with a small, understandable change. If this requires backend work beyond a simple read-only query, document the follow-up and use the existing directory CTA for now.
5. **Verify:** test all CTAs, search query submission, feature-flag copy, responsive layouts, focus states, image loading, and relevant build/tests. Review the diff and remove unrelated edits.

## Non-negotiable boundaries

- Do not alter payment, payout, authorization, privacy/consent, original photo delivery, or database schema for this marketing change.
- Do not invent events, testimonials, partner logos, counts, prices, earnings, or athlete images.
- Do not change the event search form action, method, or `search` input name.
- Do not present selfie search when `face_recognition.enabled` is false.
- Do not modify photographer application fields or submission behavior.
- Preserve working routes, navigation, footer legal links, and accessibility behavior.
- Do not refactor unrelated backend code.

## Deliverable

Provide a short report with the files changed, before/after message hierarchy, claims verified, mobile/desktop visual checks, search and CTA checks, tests/build results, and any deferred gallery-data work. State any image-rights or data limitation that prevented a proposed section from being implemented.

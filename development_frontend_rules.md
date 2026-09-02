Frontend Development Rules

These rules apply by default to Blade, HTML, CSS, Bootstrap, Minible, JavaScript, ApexCharts, forms, responsive layouts, accessibility, translations, and user-interface development tasks.

For work that also changes controllers, models, validation, authorization, database queries, APIs, or server-side behavior, read development_backend_rules.md as well.

1. Think Before Coding

Restate the visible behavior in verifiable terms.

Identify the user, screen, device sizes, states, and permissions involved.

Inspect the current layout, theme, components, assets, and conventions before implementation.

State assumptions explicitly.

Ask for clarification when layout, wording, interaction, or data meaning is materially ambiguous.

Prefer the simplest interface that solves the user’s task.

2. Keep Changes Surgical

Modify only the page, component, script, style, translation, or asset required by the request.

Do not redesign neighboring screens unless the request depends on them.

Do not replace the existing design system or add a new framework without explicit approval.

Do not rename established classes, IDs, routes, translation keys, or component interfaces unnecessarily.

Reuse existing theme components before creating new custom patterns.

Stop when the requested interface is complete and verified.

3. Respect the Existing Frontend Stack

Inspect the project before choosing an implementation.

Continue using Blade when the project uses Blade.

Continue using Bootstrap and Minible components when they are the established design system.

Use the existing icon library.

Use the existing chart library, such as ApexCharts, when charts are required.

Use existing JavaScript conventions and build tooling.

Prefer theme variables and Bootstrap utilities over large custom CSS blocks.

Do not introduce Tailwind, React, Vue, Alpine, another chart library, or another dependency unless requested or already used for that feature.

New dependencies require a clear need and explicit approval.

4. Blade Is Presentation Only

Blade templates must receive final, display-ready data.

Prohibited in Blade

@php blocks.

Authentication retrieval such as auth()->user().

Model or relationship queries.

Eloquent methods.

Collection transformations such as pluck, filter, map, sort, or values.

Calculations, data normalization, mutations, and fallback construction.

Business rules.

Complex role, permission, or status resolution.

Complex conditional expressions.

This is prohibited:

@php
$roles = auth()->user()->roles
->pluck('name')
->filter()
->values();
@endphp

The controller, service, ViewModel, or view composer must prepare the values first.

Blade may use simple presentation directives with prepared data:

@if ($showAdminDashboard)
...
@endif

@foreach ($dashboardRoleNames as $roleName)
<span class="badge bg-primary">{{ $roleName }}</span>
@endforeach

If data is not ready for direct display, it does not belong in Blade.

5. Layout and Visual Hierarchy

Make the primary user task visually obvious.

Use headings in a logical hierarchy.

Group related information into clear sections.

Keep spacing, borders, shadows, radii, colors, and card heights consistent with the theme.

Use color intentionally and sparingly.

Do not use decorative UI that competes with the content.

Avoid excessive gradients, animation, oversized icons, and dense card grids.

Prefer concise labels and plain language.

Correct grammar and inconsistent capitalization in changed interface text.

Modern does not mean visually busy. The interface must remain professional, readable, and appropriate for the organization.

6. Responsive Behavior

Design for desktop, tablet, and mobile.

Use the existing Bootstrap grid and responsive utilities.

Avoid fixed widths that break smaller screens.

Ensure cards stack logically.

Ensure long names, emails, roles, URLs, IPv6 addresses, and translated text wrap safely.

Keep tables usable through responsive containers or an appropriate mobile presentation.

Do not hide essential actions or information on small screens.

Avoid horizontal page scrolling.

7. Accessibility

Use semantic HTML.

Associate every form field with a visible label.

Maintain a logical heading structure.

Preserve keyboard navigation and visible focus states.

Use buttons for actions and links for navigation.

Provide meaningful alternative text for informative images.

Hide decorative icons from assistive technology or provide appropriate accessible labels.

Maintain readable contrast in light and dark themes.

Do not communicate status through color or icons alone.

Use ARIA only when native semantic HTML is insufficient.

8. Forms and Feedback

Clearly distinguish labels, inputs, help text, validation errors, and required fields.

Use the correct input type.

Preserve submitted values after validation errors.

Display backend validation errors next to the relevant field when possible.

Provide clear success, warning, error, loading, empty, and disabled states.

Prevent accidental duplicate submissions when the existing pattern supports it.

Do not depend on client-side validation for security or data integrity.

Do not disable actions without explaining why when the reason is useful to the user.

9. JavaScript

Write the minimum JavaScript required.

Prefer existing project utilities and conventions.

Avoid global variables.

Check that a target element exists before initializing a component.

Keep selectors stable and specific.

Avoid duplicate event listeners and duplicate component initialization.

Handle empty, missing, malformed, and zero-value data safely.

Use @json() or the project’s established safe data-transfer method for server data.

Do not construct executable JavaScript from unescaped user input.

Remove obsolete code and unused component containers when replacing an implementation.

Do not add animation unless it improves understanding or feedback.

10. Charts and Data Visualization

Use a chart only when it communicates a meaningful comparison, trend, distribution, or progress measure better than text.

Use real application data; never invent values or percentages.

Give every chart a clear title and useful context.

Keep headings, counters, and descriptions outside the chart target element.

Use restrained theme-compatible colors.

Avoid distributed rainbow colors without a semantic reason.

Format labels and tooltips clearly.

Provide a readable empty state.

Handle zero totals and division by zero safely.

Make charts responsive and accessible.

Disable unnecessary chart controls.

Prefer a progress bar or number when it communicates the result more simply than a chart.

Charts must be useful, not decorative.

11. Links, Navigation, and Actions

Confirm every route exists before linking to it.

Show actions only when the prepared authorization value permits them.

Never use empty links such as <a href="">.

Use descriptive link and button text.

Add rel="noopener noreferrer" to external links opened with target="\_blank".

Preserve the user’s expected navigation and back behavior.

Do not use a link styled as a button for an operation that changes data.

Frontend visibility does not replace backend authorization.

12. Translations and Content

Inspect the project’s language structure before adding interface text.

Use translation files when the surrounding interface is localized.

Preserve every language supported by the changed screen.

Do not delete or rename translation keys without checking their use.

Use clear, natural wording rather than literal or awkward translation.

Allow enough layout space for translated text expansion.

Keep organization names, product names, and established terminology consistent.

13. Security and Privacy

Render user-controlled values with escaped Blade syntax such as {{ }}.

Do not use {!! !!} for untrusted content.

Do not place secrets, tokens, internal paths, session IDs, or private server details in HTML or JavaScript.

Do not expose raw permission names unless the feature explicitly requires them.

Do not claim a connection is encrypted without checking the server-provided HTTPS status.

Do not manually trust forwarded IP headers.

Do not reveal hidden data merely by visually concealing it.

14. Empty, Loading, and Failure States

Every dynamic interface must consider, when applicable:

No records.

Zero values.

Missing optional data.

Loading or processing.

Validation failure.

Authorization failure.

External-service failure.

Long content.

Empty states should explain the situation without inventing data or blaming the user.

15. Verification

Before finishing:

Confirm the requested visible behavior.

Confirm the correct interface appears for each relevant role.

Check desktop, tablet, and mobile layouts.

Check light and dark modes when supported.

Check long content, empty data, zero values, and errors.

Verify links, buttons, forms, charts, and JavaScript initialization.

Verify Blade, HTML, JavaScript, and translation syntax with the smallest relevant safe checks.

Review accessibility fundamentals and keyboard operation.

Review the diff and remove unrelated changes.

Report changed files and any unverified visual assumption.

Do not claim a browser, device, or visual state was tested unless it was actually checked.

Default Frontend Working Sequence

Restate the user-facing goal and acceptance criteria.

Inspect the existing page, theme, components, scripts, translations, and permissions.

State assumptions and unclear design decisions.

Choose the simplest implementation within the existing design system.

Implement the smallest responsive and accessible change.

Verify data states, roles, interactions, and relevant screen sizes.

Stop when the goal is satisfied.

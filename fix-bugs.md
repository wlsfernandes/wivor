AI Instructions: Surgical Bug Fixes Only

Use these instructions whenever you are asked to investigate or fix an error, defect, or unexpected behavior in this project.

Primary Objective

Fix the reported bug with the smallest safe change possible.

This is a bug-fixing task only. Do not add features, redesign the system, refactor unrelated code, or modify behavior outside the reported problem.

Non-Negotiable Rules

Be surgical. Change only the files and lines directly required to fix the confirmed cause.

Preserve all existing functionality, business rules, interfaces, routes, database structures, and visual design unless the reported bug specifically requires a change.

Do not create new features, options, settings, abstractions, services, components, routes, database columns, or dependencies.

Do not perform cleanup, modernization, renaming, formatting, or refactoring outside the exact fix.

Do not alter unrelated code even when you believe it could be improved.

Do not replace an existing implementation merely because another approach appears cleaner.

Do not update packages, frameworks, lock files, environment files, build tools, or configuration unless the confirmed cause requires it and approval is given first.

Do not change authentication, authorization, permissions, payments, storage, email delivery, queues, scheduled jobs, or production configuration unless they are explicitly part of the reported bug.

Do not delete data, files, migrations, tests, logs, or existing functionality.

Never expose, copy, log, or commit passwords, API keys, tokens, private customer information, or .env values.

Preserve the project's existing coding style and architecture.

If the working tree already contains unrelated changes, preserve them and do not overwrite or revert them.

Required Workflow

1. Understand the Bug

Before editing code:

Read the complete bug report, error message, stack trace, screenshots, and reproduction steps provided.

Identify the expected behavior and the actual behavior.

Inspect the relevant code path and its direct dependencies.

Reproduce the problem when safely possible.

Determine the root cause. Do not patch only the visible symptom when the actual cause can be identified.

2. Ask Questions Only When Necessary

Ask a short numbered list of questions before making changes if any of these are unclear:

What exact action causes the error?

What should happen instead?

Does the problem occur locally, in production, or both?

Is there a complete error message or relevant log entry?

Which user role, record, page, event, order, or file is affected?

Can the bug be reproduced consistently?

Is a related existing behavior required to remain unchanged?

Ask only the questions needed for the current bug. Do not ask for information that can be safely found in the codebase or existing logs.

Stop and ask for approval if the proposed fix would require:

A database migration or destructive database operation.

A package or framework update.

A change to public routes, APIs, permissions, payment behavior, pricing, commissions, storage, email behavior, or production infrastructure.

Changes outside the area described in the bug report.

A new feature or a change in business rules.

3. Plan the Smallest Fix

Before editing, state briefly:

The confirmed or most likely root cause.

The exact files expected to change.

The smallest proposed correction.

The focused verification that will be performed.

If the root cause is not yet confirmed, continue investigating. Do not make speculative edits.

4. Implement Carefully

Edit only what is necessary for the fix.

Prefer a small correction in the existing code path.

Reuse current project patterns, validation, services, components, and helpers.

Keep existing method signatures and response formats whenever possible.

Do not introduce fallback behavior that hides errors or changes existing business rules.

Do not suppress exceptions, disable validation, weaken authorization, or remove safeguards merely to make the error disappear.

Do not add broad try/catch blocks, nullable handling, or default values without confirming that they represent the intended behavior.

Do not change generated, vendor, cached, compiled, or dependency files directly.

Laravel-Specific Safeguards

Follow the Laravel version and conventions already used by the project.

Do not edit .env; document any required environment change separately and request approval.

Do not run migrations against production or modify production data.

Do not change route names, middleware, policies, gates, request validation, model relationships, casts, events, jobs, mailables, notifications, or storage disks unless directly required by the bug.

Avoid changing composer.json, composer.lock, package.json, or package lock files for a normal bug fix.

Do not clear or rebuild production caches unless explicitly authorized.

When database data is involved, use read-only inspection first and avoid assumptions about record state.

Preserve localization keys and all supported languages when changing user-facing text.

Preserve existing Stripe/payment idempotency and webhook behavior.

Preserve secure file access, watermarks, signed URLs, order ownership, and authorization checks.

Verification Requirements

After the change:

Run the narrowest relevant existing test or command first.

Add or modify a test only when it directly proves the bug and the fix, and only if doing so does not expand scope.

Verify the exact reproduction path now works.

Check the most likely adjacent regression within the same feature.

Review the final diff and remove every unrelated change.

Confirm that no debugging statements, temporary files, credentials, or accidental formatting changes remain.

Do not claim the problem is fixed if verification could not be completed. State exactly what was and was not verified.

Prohibited Changes

Unless explicitly requested and separately approved, do not:

Add a new feature or change product behavior.

Redesign pages or alter unrelated styling.

Refactor controllers, models, services, or components.

Rename files, classes, methods, variables, routes, database fields, or translation keys.

Create a new architectural layer or abstraction.

Upgrade or install dependencies.

Modify unrelated tests to make the test suite pass.

Disable failing tests, validation, security checks, authorization, logging, or error reporting.

Make broad search-and-replace edits.

Reformat complete files for a small change.

Commit, push, deploy, merge, or modify production without explicit authorization.

Final Response Format

After completing the fix, report only:

Root Cause

A concise explanation of why the bug occurred.

Surgical Fix

Files changed.

Exact behavior corrected.

Why this was the minimum necessary change.

Verification

Tests or commands run.

Manual behavior verified.

Any verification that could not be completed.

Scope Confirmation

Confirm that:

No new functionality was added.

No unrelated code was changed.

No database, dependency, environment, route, permission, or infrastructure change was made, unless explicitly approved and listed.

Remaining Concern

Include this section only if a real unresolved risk remains. Do not propose unrelated improvements.

Bug Report Template

Use this template when assigning a bug:

BUG:

Page or area:

Steps to reproduce:
1.
2.
3.

Actual result:

Expected result:

Error message or log:

Affected user/role/record:

Environment: local / staging / production

Important behavior that must not change:

Additional evidence: screenshot / video / URL / record ID

Core Decision Rule

If a change is not necessary to correct the confirmed bug, do not make it.
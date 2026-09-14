# Wivor Development Rules

These rules apply by default to:

* New development
* Feature changes
* Bug fixes
* Laravel / PHP
* Blade
* JavaScript
* Database changes
* APIs
* Queues
* Integrations
* Stripe and payment-related code

The main principle is:

> **Always choose the simplest safe solution that solves the exact request.**

Do not make the system more complicated than necessary.

---

# 1. Inspect Before Changing

Before writing code, inspect the existing implementation.

Understand:

* Current route
* Controller
* Model
* Blade/view
* Database structure
* Existing services
* Existing JavaScript
* Existing tests
* Related business rules

Never assume architecture when the answer already exists in the project.

Reuse existing patterns whenever possible.

---

# 2. Keep Every Change Surgical

Change only what is necessary for the requested task.

Do not:

* Refactor unrelated code
* Rename unrelated methods
* Reorganize folders
* Replace working architecture
* Create unnecessary abstractions
* Modify neighboring functionality because it could be cleaner
* Add speculative future functionality
* Touch unrelated backend behavior

Preserve existing behavior unless the request explicitly changes it.

If the task can be completed safely with a small change, make the small change.

Stop when the requested behavior works.

---

# 3. Simplicity Is the Default

Always prefer:

```text
simple > clever

existing code > new architecture

small change > refactor

clear code > compressed code

direct solution > abstraction

working implementation > theoretical improvement
```

Do not create new:

* Services
* Repositories
* DTOs
* Interfaces
* Action classes
* Helpers
* Events
* Jobs
* Traits
* ViewModels
* JavaScript modules

unless they are genuinely needed or already part of the project's established architecture.

A small feature should remain a small feature.

---

# 4. Bug Fix Rule

For bug fixes, be especially surgical.

First determine:

1. What is failing?
2. Where does it fail?
3. What is the actual cause?
4. What is the smallest safe fix?

Fix the cause.

Do not use a bug fix as an opportunity to redesign surrounding code.

Do not change unrelated functionality.

After the fix, verify the original behavior still works.

---

# 5. Preserve the Backend

The backend is especially sensitive.

Do not modify backend logic unless the requested feature or fix actually requires it.

Be especially careful around:

* Authentication
* Authorization
* Stripe
* Stripe Connect
* Webhooks
* Payments
* Commissions
* Payouts
* Orders
* Queue jobs
* Email delivery
* Storage
* File processing
* Scheduled tasks

If a frontend change can solve the task without changing backend behavior, prefer that.

If backend changes are necessary, modify the smallest possible area.

---

# 6. Follow Existing Laravel Architecture

Inspect the project before introducing any structure.

Follow existing conventions for:

* Routes
* Middleware
* Controllers
* Models
* Validation
* Authorization
* Services
* Logging
* Views
* Tests

Admin controllers belong in:

```text
app/Http/Controllers/Admin
```

Frontend controllers belong in:

```text
app/Http/Controllers/Frontend
```

Do not mix admin and frontend responsibilities unless the existing project intentionally does so.

Use route-model binding where the project already uses it.

Do not introduce a different architectural pattern without a real need.

---

# 7. Controllers Must Stay Simple

Controllers should coordinate:

```text
request
↓
validation
↓
business operation
↓
response
```

Do not turn controllers into large business-logic containers.

At the same time, do not create a service merely to move five simple lines out of a controller.

Create additional architecture only when the logic is:

* Complex
* Reused
* Clearly its own workflow

---

# 8. Clear Variable Names Are Required

Always use descriptive variable names.

Good:

```php
$promoCode
$discountPercent
$discountAmountInCents
$cartSubtotalInCents
$cartTotalInCents
$photographer
$eventPhotos
$authenticatedUser
```

Avoid vague names:

```php
$data
$temp
$x
$d
$res
$obj
$val
$item2
$result1
```

A developer should understand the purpose of a variable without tracing several lines of code.

Clarity is more important than short variable names.

---

# 9. PHPDoc Is Required

PHP code should be clearly documented.

Every controller should have a useful PHPDoc description.

Every controller method should have PHPDoc explaining what it does.

Important model methods, relationships, scopes, accessors, services, jobs, and non-obvious methods should also have PHPDoc.

Example:

```php
/**
 * Apply a valid promotional code to the current cart.
 */
public function applyPromoCode(Request $request): JsonResponse
{
    //
}
```

For methods with meaningful parameters or return structures:

```php
/**
 * Calculate the promotional discount for the cart subtotal.
 *
 * @param int $cartSubtotalInCents
 * @param int $discountPercent
 * @return int
 */
private function calculateDiscountInCents(
    int $cartSubtotalInCents,
    int $discountPercent
): int {
    //
}
```

Do not write useless generated documentation such as:

```php
/**
 * Summary of index.
 */
```

PHPDoc must explain purpose, not repeat the method name.

---

# 10. Comments Should Explain Why

Use comments for:

* Business rules
* Non-obvious behavior
* Important decisions
* Workarounds
* Sensitive calculations

Do not comment every obvious line.

Bad:

```php
// Get the user
$user = $request->user();
```

Useful:

```php
// Revalidate the promo code before payment because it may have
// been disabled or expired after the cart was displayed.
```

---

# 11. Type Code Clearly

Use parameter and return types when appropriate.

Prefer:

```php
public function index(): View
```

instead of leaving the return type unclear.

Prefer explicit method signatures and imports.

Do not add complicated typing purely for appearance.

Keep it consistent with the current project and PHP version.

---

# 12. Validation

Validate all user-controlled input on the server.

Frontend validation is for user experience only.

Never trust JavaScript for:

* Prices
* Discounts
* Permissions
* Ownership
* Payment amounts
* Statuses
* Sensitive values

Use clear Laravel validation rules.

Do not add validation rules that the business requirement does not need.

For simple validation, keep it simple.

If the project already uses Form Requests for that area, follow the existing convention.

---

# 13. Database Changes

Before changing the database inspect:

* Existing model
* Existing migration structure
* Relationships
* Casts
* Constraints
* Current data usage

Do not add columns "for the future."

Do not create relationships that are not required.

Keep migrations focused.

Use database constraints only when they protect a real rule.

Never change or remove existing data behavior casually.

---

# 14. Models

Models should contain:

* Relationships
* Casts
* Scopes
* Accessors/mutators
* Model-specific behavior

Do not turn models into general service classes.

Use `$fillable` or `$guarded` intentionally.

Never pass an entire request directly into a model.

Use validated data.

Document important model properties and relationships with PHPDoc.

---

# 15. Blade and Frontend

Keep Blade simple.

Reuse:

* Existing layouts
* Existing components
* Existing Bootstrap classes
* Existing JavaScript conventions

Do not add a frontend framework for a small interaction.

For simple behavior prefer:

```text
Blade
+
existing Bootstrap
+
small JavaScript/fetch/AJAX
```

Do not redesign a page when the request asks for a small improvement.

UI changes should look natural within the existing application.

---

# 16. AJAX

AJAX should remain small and understandable.

Reuse the project's existing approach:

* `fetch()`
* Axios
* jQuery AJAX

Do not add another dependency.

The server remains the source of truth.

AJAX may update the interface, but critical values must always be validated again by the backend.

---

# 17. Payments and Stripe

Payment code requires extra caution.

Before modifying payment behavior inspect the complete existing flow.

Do not casually modify:

* Stripe Checkout
* Stripe Connect
* Webhooks
* Application fees
* Transfers
* Connected accounts
* Photographer payouts
* Commission calculations
* Order fulfillment

Change only the exact part required.

Do not redesign Stripe architecture while implementing an unrelated feature.

Never trust payment amounts supplied by JavaScript.

The server must calculate the final amount.

---

# 18. Money

Follow the application's existing money format.

If the application uses integer cents, continue using integer cents.

Example:

```php
$cartSubtotalInCents
$discountAmountInCents
$cartTotalInCents
```

Do not introduce floating-point money calculations into code that already works in cents.

Keep financial variable names explicit.

---

# 19. Authorization and Security

Never rely on the UI to provide security.

Buttons being hidden does not authorize an action.

Use the project's existing:

* Middleware
* Policies
* Gates
* Role checks
* Ownership rules

Do not weaken security to make a feature work.

Never expose:

* Passwords
* Tokens
* API secrets
* Stripe secrets
* Sensitive payment information
* Internal stack traces
* SQL errors
* Infrastructure details

---

# 20. Logging and Error Handling

Follow the existing project logging pattern.

For Wivor, use `SystemLogger` where the application already expects it.

Log enough information to diagnose the problem.

Do not log sensitive information.

Do not silently swallow exceptions.

Do not expose exception details directly to users.

Keep user-facing errors simple and understandable.

---

# 21. Transactions

Use a database transaction when multiple related writes must succeed or fail together.

Do not wrap every trivial single-model update in additional complexity unless:

* The project consistently requires it, or
* Data integrity actually requires it.

Simple CRUD should remain simple.

Atomic multi-record operations must remain safe.

---

# 22. Performance

Do not prematurely optimize.

However, avoid obvious problems such as:

* N+1 queries
* Loading thousands of records unnecessarily
* Repeated identical queries
* Full-table collection loading when `exists()` or `count()` is enough

Paginate data that can grow large.

Do not add caching unless there is a demonstrated reason.

---

# 23. Tests

Before changing important behavior, inspect existing tests.

Run the smallest relevant test set first.

Add focused tests for new or corrected behavior when appropriate.

Do not rewrite unrelated tests.

For a bug fix, ideally add a test that fails before the fix and passes after it.

Never claim a test passed unless it was actually executed.

---

# 24. Do Not Break Existing Behavior

A feature should not change behavior for users who do not use that feature.

Example:

If a promo code feature is added:

```text
customer without promo code
```

should continue through exactly the same checkout flow as before.

Always consider regression risk.

---

# 25. Dirty Worktree

Do not overwrite or discard unrelated existing changes.

Before modifying a file, inspect current changes when appropriate.

Preserve user work.

Do not run destructive Git commands unless explicitly requested.

---

# 26. Default Working Process

For every development task or bug fix:

### 1. Inspect

Understand the current implementation.

### 2. Identify

Determine exactly what needs to change.

### 3. Minimize

Choose the smallest safe solution.

### 4. Implement

Make only the required changes.

### 5. Document

Use clear variables, PHPDoc, and useful comments.

### 6. Verify

Test the requested behavior and important regressions.

### 7. Review

Review the diff for unnecessary changes.

### 8. Stop

Once the requested behavior is complete, stop.

Do not expand the scope.

---

# 27. Before Making a Sensitive Change

For sensitive areas such as:

```text
payments
Stripe
payouts
commissions
authentication
authorization
database migrations
file deletion
production configuration
```

inspect first.

If the requested change unexpectedly requires a significant architectural modification, do not silently make it.

Explain the dependency or risk before redesigning that area.

---

# 28. Final Report

After completing work, provide a short report containing:

```text
Files changed
What changed
Why it was necessary
Tests/checks executed
Results
Any important assumption or remaining risk
```

Do not produce a large report for a tiny change.

Keep the final report proportional to the work performed.

---

# Golden Rules

Always follow these rules:

```text
1. Inspect before coding.

2. Keep everything as simple as possible.

3. Make surgical changes only.

4. Do not touch unrelated functionality.

5. Preserve working backend behavior.

6. Reuse existing architecture.

7. Use clear and descriptive variable names.

8. Write useful PHPDoc.

9. Avoid unnecessary abstractions.

10. Validate important values on the server.

11. Test the exact behavior changed.

12. Review the diff.

13. Stop when the requested task is complete.
```

When there are multiple valid solutions, choose the one with:

```text
less code
fewer files
fewer dependencies
less architectural change
lower regression risk
clearer behavior
```

provided it remains secure, maintainable, and correct.

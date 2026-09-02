Backend Development Rules

These rules apply by default to Laravel, PHP, database, API, authentication, authorization, queue, command, integration, and server-side development tasks.

For work that also changes Blade, JavaScript, CSS, layouts, or user interaction, read `development_frontend_rules.md` as well.

## 1. Think Before Coding

- Restate the requested backend behavior in verifiable terms.
- State assumptions explicitly.
- Identify ambiguous business rules, permissions, statuses, and data ownership before implementation.
- Ask for clarification when choosing incorrectly could change data or access.
- Inspect the existing implementation before proposing new architecture.
- Prefer the simplest solution that fits the current application.

## 2. Keep Changes Surgical

- Change only the files and behavior required by the request.
- Preserve existing business rules unless the task explicitly changes them.
- Do not refactor neighboring code merely because it could be cleaner.
- Do not rename public methods, routes, events, commands, database columns, or configuration keys without necessity.
- Preserve unrelated user changes in a dirty worktree.
- Stop when the requested behavior is complete and verified.

## 3. Follow the Existing Laravel Architecture

- Inspect the Laravel and PHP versions, project conventions, and existing patterns before writing code.
- Routes must map requests clearly and follow the existing naming and middleware conventions.
- Controllers coordinate requests and responses; they must not become business-logic containers.
- Use services for genuine reusable workflows or complex business operations, not as speculative abstractions.
- Models define relationships, casts, scopes, and model-specific behavior without becoming catch-all service classes.
- Use policies and gates to centralize resource authorization when the project already uses them.
- Use events and listeners only when decoupling is useful to the requested behavior.
- Use jobs only for work that is genuinely asynchronous, slow, retryable, or already queued by the application.
- Keep orchestration in commands and move reusable behavior into an appropriate service when necessary.
- Do not introduce repositories, DTOs, action classes, interfaces, or other architecture unless the current project uses them or the task clearly requires them.

## 4. Controller Location and Namespace

Controllers must be separated by application area.

- Admin controllers belong in `app/Http/Controllers/Admin` and use the `App\Http\Controllers\Admin` namespace.
- Frontend controllers belong in `app/Http/Controllers/Frontend` and use the `App\Http\Controllers\Frontend` namespace.
- API controllers belong in the project's established API controller folder and namespace.
- Do not mix admin and frontend actions in the same controller.
- Apply the appropriate admin, frontend, or API middleware at the route or controller level.
- Use singular, resource-oriented controller names such as `BenefitController`.
- Extend `BaseController` when that is the application's canonical base controller.

Example locations:

```text
app/Http/Controllers/Admin/BenefitController.php
app/Http/Controllers/Frontend/BenefitController.php
```

## 5. Canonical Resource Controller Pattern

Admin and frontend resource controllers must follow the same predictable structure unless an existing project convention requires otherwise.

Use this method order when the methods are present:

1. `index`
2. `create`
3. `store`
4. `show`
5. `edit`
6. `update`
7. `destroy`
8. Controller-specific validation helper methods

Each controller must:

- Import every dependency explicitly.
- Use route-model binding for resource records when practical and consistent with the route definition.
- Use the same binding style across `show`, `edit`, `update`, and `destroy`.
- Declare parameter and return types.
- Use `View`, `RedirectResponse`, `JsonResponse`, or another precise response type as appropriate.
- Return final, display-ready data to views.
- Keep validation in a dedicated `validateData` method for simple controller validation.
- Use a dedicated Form Request instead when validation or request authorization is substantial or the project already uses Form Requests for that resource.
- Pass only validated data to `create`, `update`, or `fill`.
- Wrap create, update, delete, and any related writes in `DB::transaction(...)` so the operation succeeds or fails atomically.
- Catch `Throwable` around recoverable write operations.
- Log failed write operations through `SystemLogger` with a stable event name and useful context.
- Return the established success or error flash message without exposing exception details to the user.

Do not alternate between manually loading an ID in one action and route-model binding in another without a documented reason.

## 6. Controller Documentation

Every controller and every controller method must have a clear PHPDoc block.

Controller PHPDoc must state:

- What resource or workflow the controller handles.
- Whether it belongs to the admin, frontend, or API area.

Method PHPDoc must state:

- What the method does.
- Relevant parameters when their purpose is not obvious.
- The returned response type.
- Important exceptions only when callers are expected to handle them.

Avoid generated filler such as `Summary of index`. Write a direct description instead.

Example:

```php
/**
 * Display a listing of benefits in the admin panel.
 */
public function index(): View
{
    // ...
}
```

Comments must explain intent, business rules, or non-obvious behavior. Do not add line-by-line comments that merely repeat the code.

## 7. Controller Validation

Validate all untrusted input on the server.

For simple resource validation, use one protected validation method in the controller:

```php
/**
 * Validate data used to create or update a benefit.
 *
 * @return array<string, mixed>
 */
protected function validateData(Request $request, ?int $benefitId = null): array
{
    return $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);
}
```

- Reuse the same validation method for `store` and `update` when the rules are shared.
- Pass the current model ID when an update rule must ignore the current record for uniqueness.
- Use array-based validation rules for readability and safe composition.
- Distinguish intentionally between `required`, `nullable`, `sometimes`, and optional fields.
- Use database existence and uniqueness rules where appropriate.
- Normalize input only when required by a documented business rule.
- Never rely on frontend validation for correctness or security.
- Preserve submitted values and return useful validation messages.
- Do not add validation rules that were not requested merely because they seem preferable.
- Do not catch Laravel validation exceptions as general controller failures; validation should complete before entering the write-operation `try` block.

## 8. Atomic Writes and Transactions

- Use `DB::transaction(...)` for controller write operations.
- Include every dependent database write in the same transaction.
- Do not perform part of a multi-record workflow outside the transaction.
- Keep external network calls, email delivery, and other irreversible side effects outside the database transaction when possible.
- When an external side effect must happen after a successful commit, use the project's after-commit pattern.
- Consider row locks or other concurrency controls for counters, balances, inventory, approvals, and status transitions.
- Let exceptions escape the transaction closure so Laravel can roll back automatically.

Example:

DB::transaction(function () use ($benefit, $validated): void {
    $benefit->update($validated);
});

Delete operations must also be transactional:

DB::transaction(function () use ($benefit): void {
$benefit->delete();
});

## 9. Errors and SystemLogger

Use `SystemLogger` for recoverable controller failures.

- Do not swallow exceptions silently.
- Catch exceptions only when the application can recover, add useful context, or return an intentional response.
- Catch `Throwable` for controller write failures when this matches the established project pattern.
- Use stable, consistent event names such as `admin.benefits.store`, `admin.benefits.update`, and `admin.benefits.destroy`.
- Use consistent snake_case context keys such as `benefit_id` and `exception`.
- Include the resource ID for update and delete failures.
- Include enough context to diagnose the operation without logging passwords, tokens, payment details, personal documents, full request payloads, or unnecessary personal data.
- Preserve the original exception when wrapping or reporting it.
- Do not expose stack traces, exception messages, SQL, or infrastructure details to users.
- Use the project's established severity values and user-facing flash-message pattern.

Example:

```php
SystemLogger::log(
    'Benefit update failed',
    'error',
    'admin.benefits.update',
    [
        'benefit_id' => $benefit->id,
        'exception' => $exception->getMessage(),
    ]
);
```

## 10. Controllers and View Data

Controllers must provide final, display-ready data to views.

- Retrieve the authenticated user server-side.
- Resolve roles, permissions, status labels, fallbacks, totals, percentages, formatted values, and display booleans before rendering.
- Eager-load relationships required by the view.
- Use descriptive variable names.
- Return predictable empty collections, `null`, or zero values as appropriate.
- Do not make Blade discover or construct the data it needs.
- Paginate data that can grow large; use `get()` only when the dataset is known to remain small.

Example:

```php
$user = $request->user();
$roleNames = $user->roles()
    ->whereNotNull('name')
    ->where('name', '<>', '')
    ->pluck('name');

return view('dashboard', [
    'dashboardUserName' => $user->name,
    'dashboardRoleNames' => $roleNames->isNotEmpty()
        ? $roleNames
        : collect(['Standard User']),
    'showAdminDashboard' => $user->can('access-admin')
        || $user->hasRole('developer'),
]);
```

If preparing view data becomes genuinely complex or is reused, use a ViewModel, presenter, or view composer consistent with the project. Do not create one for a single trivial transformation.

## 11. Model Structure and Documentation

Every Eloquent model must be clean, typed where the project permits, and clearly documented.

Model PHPDoc must include:

- A direct description of what the model represents.
- Important database-backed properties using `@property` or `@property-read`.
- Relationship properties when useful to static analysis and maintenance.
- Timestamp properties when the model uses timestamps.
- A model-builder mixin or generated IDE-helper annotations only when the project already maintains them.

Do not manually add large lists of generated `where...` method annotations unless the project consistently uses and regenerates them. Generated annotations become stale easily.

Inside the model:

- Place traits immediately after the opening class brace.
- Define `$fillable` or `$guarded` intentionally.
- Define casts using the project's Laravel-version convention.
- Document every relationship, scope, accessor, mutator, and non-trivial model method.
- Declare return types for relationships and methods.
- Keep only model-specific behavior in the model.
- Never expose sensitive or privileged fields through mass assignment without explicit need.

Canonical simple model example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a benefit available in the application.
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Benefit extends Model
{
    use HasFactory;

    /**
     * Attributes that may be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];
}
```

## 12. Authorization and Ownership

- Authenticate and authorize every protected server-side action.
- Never rely on hidden buttons or absent navigation links as authorization.
- Verify record ownership when users may access only their own data.
- Apply the same authorization rule to every endpoint that performs the same operation.
- Use policies, gates, middleware, or the project's established authorization pattern.
- Return the appropriate 403, 404, validation error, or redirect response.
- Do not silently broaden administrator access or treat loosely related roles as administrators.
- Permission checks must use exact project roles and permissions. Inspect them before implementation.

## 13. Database and Eloquent

- Inspect models, scopes, relationships, casts, migrations, and existing queries before adding new ones.
- Avoid N+1 queries through deliberate eager loading.
- Use `count`, `exists`, aggregates, and database filtering instead of loading full collections unnecessarily.
- Select only required columns when it materially improves a heavy query.
- Preserve existing global scopes and soft-delete behavior unless the task explicitly changes them.
- Add indexes only for demonstrated query or integrity needs.
- Use database constraints when they protect real invariants.
- Make migrations focused, reversible when practical, and safe for existing data.
- Name foreign keys, indexes, and constraints consistently with the project.
- Do not use raw SQL when Eloquent or the query builder expresses the operation clearly.
- When raw SQL is necessary, parameterize values and document the reason.
- Confirm database-specific syntax against the project's configured database.
- Never guess what `active`, `pending`, `expired`, `published`, `returned`, or similar statuses mean. Use the definitions already established by the application.

## 14. Mass Assignment and Model Updates

- Use validated data for creates and updates.
- Respect `$fillable`, `$guarded`, casts, mutators, and model events.
- Do not pass the complete request payload directly to a model.
- Do not allow user-controlled ownership, role, permission, price, status, or approval fields without explicit authorization.
- Use explicit assignments when sensitive fields are involved.

## 15. Files, Media, and External Services

- Validate file type, size, and required metadata.
- Use the configured Laravel filesystem disk and existing storage conventions.
- Do not construct public paths by guessing.
- Confirm a file exists before reading, moving, converting, or deleting it.
- Keep destructive file operations narrowly targeted and recoverable where practical.
- Store secrets in environment or approved configuration, never in source code or logs.
- Verify webhook signatures and handle retries or duplicate delivery safely.
- Use timeouts and useful error handling for external calls.
- Do not claim an external operation succeeded until its response confirms success.

## 16. Security Defaults

- Escape output at the presentation layer.
- Protect state-changing routes with the application's authentication and CSRF conventions.
- Prevent SQL injection by using parameter binding, Eloquent, or the query builder.
- Do not trust forwarded IP headers manually; use Laravel's trusted request handling.
- Do not expose internal server addresses, paths, versions, session identifiers, or secrets.
- Treat uploaded filenames and user-controlled paths as untrusted.
- Do not weaken security configuration to make a feature work.

## 17. Performance

- Optimize only where the requested behavior or evidence justifies it.
- Eliminate obvious N+1 queries and unnecessary full-table collection loading.
- Paginate potentially large result sets.
- Queue genuinely slow work when the application supports it.
- Cache only stable, expensive data with a clear invalidation strategy.
- Do not add caching as a default response to an unmeasured problem.

## 18. Verification

Before finishing:

- Confirm the requested success path.
- Confirm controller location and namespace match the application area.
- Confirm route-model binding is consistent across resource actions.
- Confirm authorization and ownership boundaries.
- Confirm validation and relevant failure cases.
- Confirm create, update, and delete operations are transactional.
- Confirm failures use `SystemLogger` with useful, non-sensitive context.
- Confirm controller methods and model behavior have clear PHPDoc.
- Confirm only validated, fillable data reaches model writes.
- Confirm empty, missing, and zero-value behavior.
- Check for N+1 queries or unnecessary collection loading.
- Run the smallest relevant safe tests or checks.
- Review the diff and remove changes unrelated to the request.
- Report changed files, behavior, and any unverified assumption.

Do not claim a test or check passed unless it was actually run.

## Default Backend Working Sequence

1. Restate the backend goal and acceptance criteria.
2. Inspect the current route, controller, model, database, authorization, and tests.
3. State assumptions and unresolved risks.
4. Confirm whether the controller is Admin, Frontend, or API and place it accordingly.
5. Choose the smallest architecture consistent with the project.
6. Implement the canonical controller and model pattern.
7. Verify success, failure, authorization, logging, and data-integrity behavior.
8. Stop when the goal is satisfied.

## Canonical Admin CRUD Controller Example

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Benefit;
use App\Services\SystemLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/**
 * Handles Benefit CRUD operations in the admin panel.
 */
class BenefitController extends BaseController
{
    /**
     * Display a listing of benefits.
     */
    public function index(): View
    {
        $benefits = Benefit::query()
            ->orderBy('name')
            ->get();

        return view('admin.benefit.index', compact('benefits'));
    }

    /**
     * Show the form for creating a benefit.
     */
    public function create(): View
    {
        return view('admin.benefit.form');
    }

    /**
     * Store a newly created benefit.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        try {
            DB::transaction(function () use ($validated): void {
                Benefit::create($validated);
            });

            return redirect()
                ->route('admin.benefit.index')
                ->with('success', 'Benefit created successfully.');
        } catch (Throwable $exception) {
            SystemLogger::log(
                'Benefit creation failed',
                'error',
                'admin.benefits.store',
                ['exception' => $exception->getMessage()]
            );

            return back()
                ->withInput()
                ->with('error', 'Failed to create benefit.');
        }
    }

    /**
     * Show the form for editing a benefit.
     */
    public function edit(Benefit $benefit): View
    {
        return view('admin.benefit.form', compact('benefit'));
    }

    /**
     * Update an existing benefit.
     */
    public function update(Request $request, Benefit $benefit): RedirectResponse
    {
        $validated = $this->validateData($request, $benefit->id);

        try {
            DB::transaction(function () use ($benefit, $validated): void {
                $benefit->update($validated);
            });

            return redirect()
                ->route('admin.benefit.index')
                ->with('success', 'Benefit updated successfully.');
        } catch (Throwable $exception) {
            SystemLogger::log(
                'Benefit update failed',
                'error',
                'admin.benefits.update',
                [
                    'benefit_id' => $benefit->id,
                    'exception' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->with('error', 'Failed to update benefit.');
        }
    }

    /**
     * Delete an existing benefit.
     */
    public function destroy(Benefit $benefit): RedirectResponse
    {
        try {
            DB::transaction(function () use ($benefit): void {
                $benefit->delete();
            });

            return redirect()
                ->route('admin.benefit.index')
                ->with('success', 'Benefit deleted successfully.');
        } catch (Throwable $exception) {
            SystemLogger::log(
                'Benefit deletion failed',
                'error',
                'admin.benefits.destroy',
                [
                    'benefit_id' => $benefit->id,
                    'exception' => $exception->getMessage(),
                ]
            );

            return back()
                ->with('error', 'Failed to delete benefit.');
        }
    }

    /**
     * Validate data used to create or update a benefit.
     *
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?int $benefitId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
    }
}

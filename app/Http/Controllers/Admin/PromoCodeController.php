<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Handles promo code management in the admin panel.
 */
class PromoCodeController extends Controller
{
    /**
     * Display the promo code list.
     */
    public function index(): View
    {
        $promoCodes = PromoCode::query()
            ->latest()
            ->paginate(20)
            ->through(fn (PromoCode $promoCode): array => [
                'id' => $promoCode->id,
                'code' => $promoCode->code,
                'discount' => $promoCode->discount_percent . '%',
                'expiration_date' => $promoCode->expires_at->format('m/d/Y'),
                'is_active' => $promoCode->is_active,
                'status' => $promoCode->is_active ? 'Active' : 'Inactive',
            ]);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    /**
     * Show the promo code creation form.
     */
    public function create(): View
    {
        return view('admin.promo-codes.create');
    }

    /**
     * Store a new promo code.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $validated['is_active'] = $request->boolean('is_active');

        try {
            DB::transaction(fn () => PromoCode::query()->create($validated));

            return redirect()
                ->route('admin.promo-codes.index')
                ->with('success', 'Promo code created successfully.');
        } catch (Throwable $exception) {
            Log::error('Promo code creation failed.', [
                'event' => 'admin.promo_codes.store',
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Promo code could not be created. Please try again.');
        }
    }

    /**
     * Show the promo code editing form.
     */
    public function edit(PromoCode $promoCode): View
    {
        return view('admin.promo-codes.edit', compact('promoCode'));
    }

    /**
     * Update an existing promo code.
     */
    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $validated = $this->validateData($request, $promoCode->id);
        $validated['is_active'] = $request->boolean('is_active');

        try {
            DB::transaction(fn () => $promoCode->update($validated));

            return redirect()
                ->route('admin.promo-codes.index')
                ->with('success', 'Promo code updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Promo code update failed.', [
                'event' => 'admin.promo_codes.update',
                'promo_code_id' => $promoCode->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Promo code could not be updated. Please try again.');
        }
    }

    /**
     * Delete a promo code.
     */
    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        try {
            DB::transaction(fn () => $promoCode->delete());

            return redirect()
                ->route('admin.promo-codes.index')
                ->with('success', 'Promo code deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Promo code deletion failed.', [
                'event' => 'admin.promo_codes.destroy',
                'promo_code_id' => $promoCode->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Promo code could not be deleted. Please try again.');
        }
    }

    /**
     * Validate data used to create or update a promo code.
     *
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?int $promoCodeId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promo_codes', 'code')->ignore($promoCodeId),
            ],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'expires_at' => ['required', 'date'],
        ]);
    }
}
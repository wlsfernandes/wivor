<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_promo_codes(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.promo-codes.store'), [
                'code' => 'SUMMER20',
                'discount_percent' => 20,
                'is_active' => '1',
                'expires_at' => '2026-09-30',
            ])
            ->assertRedirect(route('admin.promo-codes.index'))
            ->assertSessionHas('success');

        $promoCode = PromoCode::query()->where('code', 'SUMMER20')->firstOrFail();

        $this->assertTrue($promoCode->is_active);
        $this->assertSame('2026-09-30', $promoCode->expires_at->format('Y-m-d'));

        $this->actingAs($admin)
            ->get(route('admin.promo-codes.index'))
            ->assertOk()
            ->assertSee('SUMMER20')
            ->assertSee('20%')
            ->assertSee('09/30/2026')
            ->assertSee('Active');

        $this->actingAs($admin)
            ->put(route('admin.promo-codes.update', $promoCode), [
                'code' => 'WELCOME10',
                'discount_percent' => 10,
                'expires_at' => '2026-10-31',
            ])
            ->assertRedirect(route('admin.promo-codes.index'));

        $promoCode->refresh();
        $this->assertSame('WELCOME10', $promoCode->code);
        $this->assertFalse($promoCode->is_active);
        $this->assertSame('2026-10-31', $promoCode->expires_at->format('Y-m-d'));

        $this->actingAs($admin)
            ->delete(route('admin.promo-codes.destroy', $promoCode))
            ->assertRedirect(route('admin.promo-codes.index'));

        $this->assertDatabaseMissing('promo_codes', ['id' => $promoCode->id]);
    }

    public function test_promo_code_validation_rejects_duplicates_and_invalid_discounts(): void
    {
        $admin = $this->admin();
        PromoCode::query()->create([
            'code' => 'SUMMER20',
            'discount_percent' => 20,
            'is_active' => true,
            'expires_at' => '2026-09-30',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.promo-codes.store'), [
                'code' => 'SUMMER20',
                'discount_percent' => 0,
                'expires_at' => '2026-10-31',
            ])
            ->assertSessionHasErrors(['code', 'discount_percent']);

        $this->actingAs($admin)
            ->post(route('admin.promo-codes.store'), [
                'code' => 'TOO-MUCH',
                'discount_percent' => 101,
                'expires_at' => '2026-10-31',
            ])
            ->assertSessionHasErrors(['discount_percent']);
    }

    public function test_admin_can_keep_the_same_code_when_updating(): void
    {
        $admin = $this->admin();
        $promoCode = PromoCode::query()->create([
            'code' => 'SUMMER20',
            'discount_percent' => 20,
            'is_active' => true,
            'expires_at' => '2026-09-30',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.promo-codes.update', $promoCode), [
                'code' => 'SUMMER20',
                'discount_percent' => 25,
                'is_active' => '1',
                'expires_at' => '2026-10-31',
            ])
            ->assertRedirect(route('admin.promo-codes.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('promo_codes', [
            'id' => $promoCode->id,
            'code' => 'SUMMER20',
            'discount_percent' => 25,
        ]);
    }

    public function test_promo_code_admin_routes_require_admin_access(): void
    {
        $promoCode = PromoCode::query()->create([
            'code' => 'SUMMER20',
            'discount_percent' => 20,
            'is_active' => true,
            'expires_at' => '2026-09-30',
        ]);

        $this->get(route('admin.promo-codes.index'))->assertRedirect(route('login'));

        /** @var User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user)
            ->get(route('admin.promo-codes.edit', $promoCode))
            ->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));

        return $admin;
    }
}
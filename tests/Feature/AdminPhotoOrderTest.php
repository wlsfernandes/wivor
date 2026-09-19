<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPhotoOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reconcile_a_photo_order_with_stripe_identifiers(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));
        $event = Event::create([
            'title' => 'City Run',
            'slug' => 'city-run',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'country_code' => 'US',
        ]);
        $order = Order::create([
            'event_id' => $event->id,
            'customer_email' => 'buyer@example.com',
            'currency' => 'usd',
            'photo_count' => 2,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 2000,
            'commission_percentage' => 20,
            'total_cents' => 2000,
            'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
            'stripe_checkout_session_id' => 'cs_test_reconcile',
            'stripe_payment_intent_id' => 'pi_test_reconcile',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('buyer@example.com')
            ->assertSee('$20.00 USD')
            ->assertSee('cs_test_reconcile')
            ->assertSee('pi_test_reconcile');
    }

    public function test_guest_cannot_open_the_photo_order_reconciliation_page(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_open_the_payment_process_manual(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));

        $this->actingAs($admin)
            ->get(route('payments.manual'))
            ->assertOk()
            ->assertSee('How customer payments and photographer payouts work')
            ->assertSee('Automatic photographer transfers')
            ->assertSee('Important refund limitation')
            ->assertSee('Payment Process Manual');
    }

    public function test_guest_cannot_open_the_payment_process_manual(): void
    {
        $this->get(route('payments.manual'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_open_the_payment_process_manual(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('payments.manual'))
            ->assertForbidden();
    }
}

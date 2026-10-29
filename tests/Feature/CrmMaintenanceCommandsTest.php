<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmMaintenanceCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_import_adds_paid_order_emails_once_without_consent(): void
    {
        $event = $this->event();
        $this->order($event, Order::PAYMENT_PAID, 'Buyer@Example.com');
        $this->order($event, Order::PAYMENT_PAID, 'buyer@example.com');
        $this->order($event, Order::PAYMENT_PENDING, 'abandoned@example.com');
        $this->order($event, Order::PAYMENT_PAID, null);

        $this->artisan('crm:import-buyers')->assertSuccessful();
        $this->artisan('crm:import-buyers')->assertSuccessful();

        $contact = CrmContact::sole();
        $this->assertSame('buyer@example.com', $contact->email_normalized);
        $this->assertSame(CrmContact::SOURCE_ORDER, $contact->first_source);
        $this->assertDatabaseCount('crm_contact_consents', 0);
        $this->assertDatabaseCount('crm_event_contacts', 0);
    }

    public function test_buyer_import_adds_customer_accounts_with_names_and_keeps_existing_values(): void
    {
        $newCustomer = User::factory()->create(['email' => 'new@example.com']);
        Customer::create(['user_id' => $newCustomer->id, 'first_name' => 'Nia', 'last_name' => 'Costa']);
        $knownCustomer = User::factory()->create(['email' => 'known@example.com']);
        Customer::create(['user_id' => $knownCustomer->id, 'first_name' => 'Different', 'last_name' => 'Name']);
        CrmContact::create(['email_normalized' => 'known@example.com', 'email_original' => 'known@example.com', 'first_name' => 'Kim']);

        $this->artisan('crm:import-buyers')->assertSuccessful();

        $newContact = CrmContact::where('email_normalized', 'new@example.com')->sole();
        $this->assertSame('Nia', $newContact->first_name);
        $this->assertSame(CrmContact::SOURCE_CUSTOMER_ACCOUNT, $newContact->first_source);
        $knownContact = CrmContact::where('email_normalized', 'known@example.com')->sole();
        $this->assertSame('Kim', $knownContact->first_name);
        $this->assertSame('Name', $knownContact->last_name);
    }

    public function test_purge_deletes_files_of_imports_finished_more_than_thirty_days_ago(): void
    {
        Storage::fake('local');
        $old = $this->import(CrmImport::STATUS_COMPLETED, finishedDaysAgo: 31, withErrorReport: true);
        $recent = $this->import(CrmImport::STATUS_COMPLETED, finishedDaysAgo: 29);

        $this->artisan('crm:purge-import-files')->assertSuccessful();

        Storage::disk('local')->assertMissing($old->disk_path);
        Storage::disk('local')->assertMissing($old->error_report_path);
        $this->assertNotNull($old->refresh()->files_purged_at);
        Storage::disk('local')->assertExists($recent->disk_path);
        $this->assertNull($recent->refresh()->files_purged_at);
    }

    public function test_purge_fails_imports_never_started_within_thirty_days(): void
    {
        Storage::fake('local');
        $abandoned = $this->import(CrmImport::STATUS_PENDING, createdDaysAgo: 31);
        $running = $this->import(CrmImport::STATUS_RUNNING, createdDaysAgo: 31);

        $this->artisan('crm:purge-import-files')->assertSuccessful();

        $abandoned->refresh();
        $this->assertSame(CrmImport::STATUS_FAILED, $abandoned->status);
        $this->assertNotNull($abandoned->files_purged_at);
        Storage::disk('local')->assertMissing($abandoned->disk_path);
        Storage::disk('local')->assertExists($running->disk_path);
    }

    public function test_purge_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('crm:purge-import-files');
    }

    private function import(string $status, int $createdDaysAgo = 40, ?int $finishedDaysAgo = null, bool $withErrorReport = false): CrmImport
    {
        $diskPath = 'crm-imports/'.uniqid().'.csv';
        Storage::disk('local')->put($diskPath, "Email\n");
        $errorReportPath = null;

        if ($withErrorReport) {
            $errorReportPath = 'crm-imports/errors/'.uniqid().'.csv';
            Storage::disk('local')->put($errorReportPath, "Line,Email,Error\n");
        }

        $import = CrmImport::create([
            'event_id' => $this->event()->id,
            'original_filename' => 'participants.csv',
            'disk_path' => $diskPath,
            'file_sha256' => str_repeat('a', 64),
            'mapping' => ['email' => 'Email'],
            'data_source' => 'Organizer export',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ]);
        $import->forceFill([
            'status' => $status,
            'error_report_path' => $errorReportPath,
            'created_at' => now()->subDays($createdDaysAgo),
            'finished_at' => $finishedDaysAgo === null ? null : now()->subDays($finishedDaysAgo),
        ])->save();

        return $import;
    }

    private function order(Event $event, string $paymentStatus, ?string $customerEmail): Order
    {
        return Order::create([
            'event_id' => $event->id,
            'customer_email' => $customerEmail,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 1000,
            'commission_percentage' => 20,
            'total_cents' => 1000,
            'payment_status' => $paymentStatus,
            'fulfillment_status' => Order::FULFILLMENT_PENDING,
        ]);
    }

    private function event(): Event
    {
        return Event::create(['title' => 'River Run', 'slug' => Event::generateUniqueSlug('River Run'), 'content' => 'CRM fixture.']);
    }
}

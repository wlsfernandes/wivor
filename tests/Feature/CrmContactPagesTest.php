<?php

namespace Tests\Feature;

use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmSuppression;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_list_searches_by_email_or_name(): void
    {
        $this->contact('ana@example.com', 'Ana');
        $this->contact('bo@example.com', 'Bo');

        $this->actingAs($this->admin())
            ->get(route('admin.crm.contacts.index', ['search' => 'ana']))
            ->assertOk()
            ->assertSee('ana@example.com')
            ->assertDontSee('bo@example.com');
    }

    public function test_contact_list_filters_by_event(): void
    {
        $event = $this->event('River Run');
        $this->contact('ana@example.com', 'Ana')->events()->attach($event, ['bib_number' => '1']);
        $this->contact('bo@example.com', 'Bo');

        $this->actingAs($this->admin())
            ->get(route('admin.crm.contacts.index', ['event_id' => $event->id]))
            ->assertOk()
            ->assertSee('ana@example.com')
            ->assertDontSee('bo@example.com');
    }

    public function test_contact_detail_shows_events_consents_and_suppressions(): void
    {
        $contact = $this->contact('ana@example.com', 'Ana');
        $contact->events()->attach($this->event('River Run'), ['bib_number' => '4321', 'division' => 'F30']);
        $contact->consents()->create([
            'category' => CrmContactConsent::CATEGORY_MARKETING,
            'status' => CrmContactConsent::STATUS_GRANTED,
            'source' => 'CSV import #9',
            'recorded_at' => now(),
        ]);
        CrmSuppression::create([
            'crm_contact_id' => $contact->id,
            'email_normalized' => 'ana@example.com',
            'reason' => CrmSuppression::REASON_BOUNCE,
            'category' => CrmContactConsent::CATEGORY_MARKETING,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.crm.contacts.show', $contact))
            ->assertOk()
            ->assertSee('River Run')
            ->assertSee('4321')
            ->assertSee('F30')
            ->assertSee('CSV import #9')
            ->assertSee('Bounce');
    }

    public function test_event_participant_list_shows_only_that_event(): void
    {
        $riverRun = $this->event('River Run');
        $this->contact('ana@example.com', 'Ana')->events()->attach($riverRun, ['bib_number' => '101']);
        $this->contact('bo@example.com', 'Bo')->events()->attach($this->event('City Marathon'), ['bib_number' => '7']);

        $this->actingAs($this->admin())
            ->get(route('admin.crm.events.participants', $riverRun))
            ->assertOk()
            ->assertSee('ana@example.com')
            ->assertSee('101')
            ->assertDontSee('bo@example.com');
    }

    private function contact(string $email, string $firstName): CrmContact
    {
        return CrmContact::create(['email_normalized' => $email, 'email_original' => $email, 'first_name' => $firstName]);
    }

    private function event(string $title): Event
    {
        return Event::create(['title' => $title, 'slug' => Event::generateUniqueSlug($title), 'content' => 'CRM fixture.']);
    }

    private function admin(): User
    {
        /** @var User $admin */
        $admin = User::factory()->createOne();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
    }
}

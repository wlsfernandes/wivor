<?php

namespace Tests\Feature;

use App\Models\CrmCampaign;
use App\Models\CrmContact;
use App\Models\Event;
use App\Services\CrmCampaignRenderer;
use App\Services\CrmRecipientValues;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmCampaignRendererTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['crm.postal_address' => "WiVor Photos\n1 Finish Line Rd\nAtlanta, GA 30301"]);
    }

    public function test_recipient_values_are_escaped_and_event_links_are_real_routes(): void
    {
        $event = $this->event();
        $contact = CrmContact::create(['email_normalized' => 'a@example.com', 'email_original' => 'a@example.com', 'first_name' => '<script>Ana</script>']);
        $contact->events()->attach($event, ['bib_number' => '101']);

        $email = app(CrmCampaignRenderer::class)->render(
            $this->campaign('Hi {{first_name}}', '<p>Hello {{first_name}}, bib {{bib_number}}. <a href="{{photo_gallery_url}}">Photos</a></p>'),
            CrmRecipientValues::forContact($contact, $event),
            'https://wivorphotos.com/unsubscribe/token'
        );

        $this->assertSame('Hi <script>Ana</script>', $email->subject);
        $this->assertStringContainsString('Hello &lt;script&gt;Ana&lt;/script&gt;, bib 101.', $email->html);
        $this->assertStringContainsString('href="'.route('events.photos.index', $event).'"', $email->html);
        $this->assertStringNotContainsString('<script>', $email->html);
    }

    public function test_line_breaks_from_contact_data_cannot_reach_the_subject_header(): void
    {
        $contact = CrmContact::create(['email_normalized' => 'a@example.com', 'email_original' => 'a@example.com', 'first_name' => "Ana\r\nBcc: victim@example.com"]);

        $email = app(CrmCampaignRenderer::class)->render($this->campaign('Hi {{first_name}}', '<p>Body</p>'), CrmRecipientValues::forContact($contact, null), null);

        $this->assertSame('Hi Ana Bcc: victim@example.com', $email->subject);
    }

    public function test_footer_has_postal_address_reason_and_unsubscribe_link(): void
    {
        $email = app(CrmCampaignRenderer::class)->render(
            $this->campaign('Hello', '<p>Body</p>'),
            CrmRecipientValues::forContact($this->contact(), $this->event()),
            'https://wivorphotos.com/unsubscribe/token'
        );

        $this->assertStringContainsString('1 Finish Line Rd', $email->html);
        $this->assertStringContainsString('because you took part in River Run', $email->html);
        $this->assertStringContainsString('href="https://wivorphotos.com/unsubscribe/token"', $email->html);
        $this->assertStringContainsString('Unsubscribe: https://wivorphotos.com/unsubscribe/token', $email->text);
        $this->assertStringContainsString('1 Finish Line Rd', $email->text);
    }

    public function test_missing_postal_address_is_flagged_in_the_email(): void
    {
        config(['crm.postal_address' => null]);

        $email = app(CrmCampaignRenderer::class)->render($this->campaign('Hello', '<p>Body</p>'), CrmRecipientValues::forContact($this->contact(), null), null);

        $this->assertStringContainsString('POSTAL ADDRESS NOT CONFIGURED', $email->html);
    }

    public function test_html_injected_past_the_editor_is_removed_at_render(): void
    {
        $email = app(CrmCampaignRenderer::class)->render(
            $this->campaign('Hello', '<p onmouseover="x()">Body</p><script>steal()</script><a href="javascript:alert(1)">x</a>'),
            CrmRecipientValues::forContact($this->contact(), null),
            null
        );

        $this->assertStringNotContainsString('steal()', $email->html);
        $this->assertStringNotContainsString('onmouseover', $email->html);
        $this->assertStringNotContainsString('javascript:', $email->html);
    }

    public function test_plain_text_is_generated_when_no_text_body_is_written(): void
    {
        $email = app(CrmCampaignRenderer::class)->render(
            $this->campaign('Hello', '<p>Hi {{first_name}}, see <a href="https://wivorphotos.com">photos</a>.</p>'),
            CrmRecipientValues::forContact($this->contact(), null),
            null
        );

        $this->assertStringStartsWith('Hi Ana, see photos (https://wivorphotos.com).', $email->text);
    }

    public function test_written_text_body_is_personalized_and_used(): void
    {
        $campaign = $this->campaign('Hello', '<p>HTML</p>');
        $campaign->text_body = 'Text for {{first_name}}';

        $email = app(CrmCampaignRenderer::class)->render($campaign, CrmRecipientValues::forContact($this->contact(), null), null);

        $this->assertStringStartsWith('Text for Ana', $email->text);
    }

    public function test_call_to_action_resolves_placeholders_and_rejects_unsafe_urls(): void
    {
        $event = $this->event();
        $values = CrmRecipientValues::forContact($this->contact(), $event);
        $renderer = app(CrmCampaignRenderer::class);
        $campaign = $this->campaign('Hello', '<p>Body</p>');
        $campaign->cta_label = 'See photos';
        $campaign->cta_url = '{{photo_gallery_url}}';

        $email = $renderer->render($campaign, $values, null);
        $this->assertStringContainsString('href="'.route('events.photos.index', $event).'"', $email->html);
        $this->assertStringContainsString('See photos: '.route('events.photos.index', $event), $email->text);

        $campaign->cta_url = 'javascript:alert(1)';
        $this->assertStringNotContainsString('See photos', $renderer->render($campaign, $values, null)->html);
    }

    private function campaign(string $subject, string $bodyHtml): CrmCampaign
    {
        return new CrmCampaign([
            'title' => 'Test',
            'category' => CrmCampaign::CATEGORY_EVENT_NOTIFICATION,
            'audience_type' => CrmCampaign::AUDIENCE_EVENTS,
            'subject' => $subject,
            'preview_text' => 'Your photos are ready',
            'body_html' => $bodyHtml,
        ]);
    }

    private function contact(): CrmContact
    {
        return CrmContact::create(['email_normalized' => 'ana@example.com', 'email_original' => 'ana@example.com', 'first_name' => 'Ana']);
    }

    private function event(): Event
    {
        return Event::create(['title' => 'River Run', 'slug' => 'river-run', 'content' => 'CRM fixture.', 'date_of_event' => '2026-10-10']);
    }
}

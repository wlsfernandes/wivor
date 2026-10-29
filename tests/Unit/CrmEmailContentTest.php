<?php

namespace Tests\Unit;

use App\Services\CrmHtmlSanitizer;
use App\Services\CrmPersonalizer;
use App\Services\CrmPlainText;
use Tests\TestCase;

class CrmEmailContentTest extends TestCase
{
    public function test_sanitizer_removes_scripts_event_handlers_and_javascript_links(): void
    {
        $html = (new CrmHtmlSanitizer)->sanitizeDraft(
            '<p onclick="steal()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">x</a><iframe src="https://evil.test"></iframe>'
        );

        $this->assertSame('<p>Hi</p><a rel="noopener noreferrer">x</a>', $html);
    }

    public function test_sanitizer_keeps_email_formatting_and_safe_links(): void
    {
        $html = '<h2>Race day</h2><p><strong>Bold</strong> <em>it</em> <a href="https://wivorphotos.com/events">link</a></p><ul><li>One</li></ul><img src="https://cdn.example.com/a.jpg" alt="Finish">';

        $this->assertSame(
            '<h2>Race day</h2><p><strong>Bold</strong> <em>it</em> <a href="https://wivorphotos.com/events" rel="noopener noreferrer">link</a></p><ul><li>One</li></ul><img src="https://cdn.example.com/a.jpg" alt="Finish" />',
            (new CrmHtmlSanitizer)->sanitizeDraft($html)
        );
    }

    public function test_draft_keeps_placeholder_links_but_final_html_drops_non_absolute_links(): void
    {
        $sanitizer = new CrmHtmlSanitizer;

        $this->assertStringContainsString('href="{{photo_gallery_url}}"', $sanitizer->sanitizeDraft('<a href="{{photo_gallery_url}}">Photos</a>'));
        $this->assertSame('<a rel="noopener noreferrer">Photos</a>', $sanitizer->sanitizeFinal('<a href="/relative">Photos</a>'));
    }

    public function test_sanitizer_drops_insecure_images(): void
    {
        $this->assertSame('<img alt="x" />', (new CrmHtmlSanitizer)->sanitizeDraft('<img src="http://insecure.test/a.png" alt="x">'));
    }

    public function test_personalizer_finds_unknown_placeholders(): void
    {
        $this->assertSame(
            ['{{password}}'],
            (new CrmPersonalizer)->unknownPlaceholders('Hi {{first_name}}, your {{password}} and {{ bib_number }}')
        );
    }

    public function test_personalizer_lists_event_specific_placeholders(): void
    {
        $this->assertSame(
            ['{{event_name}}', '{{bib_number}}'],
            (new CrmPersonalizer)->eventPlaceholders('{{first_name}} ran {{event_name}} with {{ bib_number }}')
        );
    }

    public function test_personalizer_escapes_values_in_html_and_uses_fallbacks(): void
    {
        $personalizer = new CrmPersonalizer;

        $this->assertSame(
            'Hi &lt;b&gt;Ana&lt;/b&gt;, bib ',
            $personalizer->renderHtml('Hi {{first_name}}, bib {{bib_number}}', ['first_name' => '<b>Ana</b>'])
        );
        $this->assertSame('Hi there', $personalizer->renderText('Hi {{first_name}}', []));
    }

    public function test_personalizer_text_rendering_does_not_escape(): void
    {
        $this->assertSame('Hi Zoë & Bo', (new CrmPersonalizer)->renderText('Hi {{first_name}}', ['first_name' => 'Zoë & Bo']));
    }

    public function test_plain_text_keeps_paragraphs_lists_and_link_targets(): void
    {
        $text = CrmPlainText::fromHtml('<h2>Photos ready</h2><p>See <a href="https://wivorphotos.com/e">your photos</a>.</p><ul><li>One</li><li>Two</li></ul><p>A&amp;B</p>');

        $this->assertSame("Photos ready\n\nSee your photos (https://wivorphotos.com/e).\n\n- One\n- Two\n\nA&B", $text);
    }
}

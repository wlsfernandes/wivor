<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandAssetsTest extends TestCase
{
    public function test_public_pages_load_the_alata_font_and_brand_stylesheet(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('family=Alata', false)
            ->assertSee('href="'.asset('assets/css/wivor-brand.css').'"', false);
    }

    public function test_brand_stylesheet_defines_every_specified_color(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        foreach (['#ff5722', '#ff7e55', '#303841', '#eeeeee', '#ffffff', '#d4d4d4'] as $color) {
            $this->assertStringContainsString($color, $stylesheet);
        }
        $this->assertStringContainsString("'Alata'", $stylesheet);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class HowToUploadPhotosTest extends TestCase
{
    public function test_public_upload_guide_matches_the_photographer_workflow(): void
    {
        $response = $this->get(route('how-to-upload-photos'))
            ->assertOk()
            ->assertSee('How to Upload Photos')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('events.index').'"', false)
            ->assertSeeInOrder([
                'Go to Login',
                'Search your events',
                'Review the photo recommendations',
                'Drop JPEGs and click Add Photos',
                'Review the processed photos',
                'Publish ready photos',
            ]);

        foreach (range(1, 6) as $step) {
            $response->assertSee('src="'.asset("assets/images/manual/{$step}.png").'"', false);
        }
    }

    public function test_photographer_page_links_to_the_upload_guide(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('href="'.route('how-to-upload-photos').'"', false);
    }
}

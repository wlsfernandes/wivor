<?php

namespace Tests\Feature;

use Tests\TestCase;

class HowToUploadPhotosTest extends TestCase
{
    public function test_public_upload_guide_matches_the_photographer_workflow(): void
    {
        $this->get(route('how-to-upload-photos'))
            ->assertOk()
            ->assertSee('How to Upload Photos')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('events.index').'"', false)
            ->assertSeeInOrder([
                'Go to Login',
                'Search your events',
                'Upload photos',
                'Review the photo recommendations',
                'Drop JPEGs and add photos',
                'Add Photos',
                'Publish Ready Photos',
            ]);
    }

    public function test_photographer_page_links_to_the_upload_guide(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('href="'.route('how-to-upload-photos').'"', false);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Order;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CheckoutTestSeeder extends Seeder
{
    private const PHOTOGRAPHER_EMAIL = 'photographer@test.wivor.local';

    private const PHOTOGRAPHER_PASSWORD = 'password';

    /**
     * Replace all events, photographers, and their dependent orders with one
     * complete checkout fixture. This destructive seeder is intentionally not
     * registered in DatabaseSeeder and must be run explicitly.
     */
    public function run(): void
    {
        $timezone = 'America/New_York';
        $now = now($timezone);
        $salesCloseAt = $now->copy()->addDays((int) config('photo_uploads.sales_window_days', 60));
        $eventUuid = (string) Str::uuid();
        $photographerUuid = (string) Str::uuid();
        $disk = Storage::disk(config('photo_uploads.disk'));
        $storedKeys = [];

        $fixtures = [
            ['file' => 'wivor_photo1.jpg', 'title' => 'Race Day Start'],
            ['file' => 'wivor_photo2.jpg', 'title' => 'Athlete in Motion'],
            ['file' => 'wivor_photo3.jpg', 'title' => 'Competition Moment'],
            ['file' => 'wivor_photo4.jpg', 'title' => 'Approaching the Finish'],
            ['file' => 'marathon.jpg', 'title' => 'Marathon Challenge'],
        ];

        try {
            $photoFixtures = [];

            foreach ($fixtures as $fixture) {
                $sourcePath = public_path('assets/images/gallery/'.$fixture['file']);
                $contents = is_file($sourcePath) ? file_get_contents($sourcePath) : false;
                $dimensions = $contents !== false ? getimagesizefromstring($contents) : false;

                if ($contents === false || $dimensions === false) {
                    throw new RuntimeException("Unable to read checkout fixture image: {$sourcePath}");
                }

                $photoUuid = (string) Str::uuid();
                $keyPrefix = "events/{$eventUuid}/photographers/{$photographerUuid}/photos/{$photoUuid}";
                $keys = [
                    'original_key' => "{$keyPrefix}/original.jpg",
                    'preview_key' => "{$keyPrefix}/preview.jpg",
                    'thumbnail_key' => "{$keyPrefix}/thumbnail.jpg",
                ];

                foreach ($keys as $key) {
                    $storedKeys[] = $key;

                    if (! $disk->put($key, $contents, [
                        'visibility' => 'private',
                        'ContentType' => 'image/jpeg',
                    ])) {
                        throw new RuntimeException("Unable to store checkout fixture image: {$key}");
                    }
                }

                $photoFixtures[] = [
                    'uuid' => $photoUuid,
                    'original_filename' => $fixture['file'],
                    'title' => $fixture['title'],
                    'alt_text' => $fixture['title'].' at the Wivor checkout test event',
                    'caption' => 'Checkout and download test photo.',
                    'copyright_notice' => '© Checkout Photographer / Wivor',
                    ...$keys,
                    'detected_mime' => $dimensions['mime'] ?? 'image/jpeg',
                    'file_size' => strlen($contents),
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                    'color_mode' => 'RGB',
                    'checksum' => hash('sha256', $contents),
                ];
            }

            [$event, $photographer] = DB::transaction(function () use (
                $eventUuid,
                $photographerUuid,
                $now,
                $salesCloseAt,
                $timezone,
                $photoFixtures
            ): array {
                // Orders restrict deletion of their event and photographer records.
                Order::query()->delete();
                Event::query()->delete();
                Photographer::query()->delete();

                $user = User::updateOrCreate(
                    ['email' => self::PHOTOGRAPHER_EMAIL],
                    [
                        'name' => 'Checkout Photographer',
                        'password' => Hash::make(self::PHOTOGRAPHER_PASSWORD),
                    ]
                );
                $user->forceFill(['email_verified_at' => $now])->save();

                $photographerRole = Role::firstOrCreate(['name' => 'photographer']);
                $user->roles()->syncWithoutDetaching([$photographerRole->id]);

                $photographer = new Photographer([
                    'user_id' => $user->id,
                    'first_name' => 'Checkout',
                    'last_name' => 'Photographer',
                    'phone' => '555-0100',
                    'camera_model' => 'Wivor Test Camera',
                    'profile_url' => 'https://wivor.com',
                    'about' => 'Approved test photographer for the Wivor checkout flow.',
                    'city' => 'Atlanta',
                    'state' => 'GA',
                    'zipcode' => '30303',
                    'age_confirmed_at' => $now,
                    'terms_accepted_at' => $now,
                ]);
                $photographer->forceFill([
                    'uuid' => $photographerUuid,
                    'status' => Photographer::STATUS_APPROVED,
                    'reviewed_at' => $now,
                    'stripe_onboarding_status' => Photographer::STRIPE_NOT_STARTED,
                ])->save();

                $event = new Event([
                    'title' => 'Wivor Checkout Test Event',
                    'slug' => 'wivor-checkout-test-event',
                    'sport' => 'Running',
                    'published' => true,
                    'status' => Event::STATUS_PUBLISHED,
                    'published_at' => $now,
                    'gallery_published_at' => $now,
                    'sales_close_at' => $salesCloseAt,
                    'price_cents' => 1000,
                    'date_of_event' => $now->toDateString(),
                    'starts_at' => $now->copy()->subHours(2),
                    'ends_at' => $now->copy()->subHour(),
                    'photos_live_at' => $now,
                    'timezone' => $timezone,
                    'venue_name' => 'Wivor Test Park',
                    'city' => 'Atlanta',
                    'state' => 'GA',
                    'country_code' => 'US',
                    'content' => 'A development-only event for testing photo selection, Stripe Checkout, and downloads.',
                    'summary' => 'Five purchasable photos for the complete Wivor checkout test.',
                ]);
                $event->forceFill(['uuid' => $eventUuid])->save();

                $assignment = EventAssignment::create([
                    'event_id' => $event->id,
                    'photographer_id' => $photographer->id,
                    'status' => 'approved',
                    'upload_deadline_at' => $now->copy()->addDays(3),
                    'rights_confirmed_at' => $now,
                ]);

                $batch = UploadBatch::create([
                    'uuid' => (string) Str::uuid(),
                    'event_id' => $event->id,
                    'photographer_id' => $photographer->id,
                    'assignment_id' => $assignment->id,
                    'selected_count' => count($photoFixtures),
                    'uploaded_count' => count($photoFixtures),
                    'ready_count' => 0,
                    'rejected_count' => 0,
                    'published_count' => count($photoFixtures),
                    'status' => 'completed',
                    'started_at' => $now,
                    'completed_at' => $now,
                ]);

                foreach ($photoFixtures as $photoFixture) {
                    Photo::create([
                        ...$photoFixture,
                        'event_id' => $event->id,
                        'photographer_id' => $photographer->id,
                        'assignment_id' => $assignment->id,
                        'upload_batch_id' => $batch->id,
                        'status' => Photo::STATUS_PUBLISHED,
                        'sale_count' => 0,
                        'uploaded_at' => $now,
                        'processing_started_at' => $now,
                        'processed_at' => $now,
                        'published_at' => $now,
                        'expires_at' => $salesCloseAt,
                    ]);
                }

                return [$event, $photographer];
            });
        } catch (Throwable $exception) {
            if ($storedKeys !== []) {
                $disk->delete($storedKeys);
            }

            throw $exception;
        }

        $this->command?->info('Checkout test data created successfully.');
        $this->command?->line('Event: '.route('events.show', ['event' => $event->slug]));
        $this->command?->line('Photographer: '.$photographer->full_name);
        $this->command?->line('Login: '.self::PHOTOGRAPHER_EMAIL.' / '.self::PHOTOGRAPHER_PASSWORD);
        $this->command?->warn('This seed deleted every existing order, event, and photographer record.');
    }
}

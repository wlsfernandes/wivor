<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Photo;
use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;
use Throwable;

class SearchEventFacesCommand extends Command
{
    private const MAX_RESULTS = 100;

    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    protected $signature = 'face-recognition:search-event
        {event : Event ID or UUID}
        {image : Local JPEG or PNG path}
        {--threshold=90 : Minimum similarity from 0 to 100}
        {--max=50 : Maximum matches from 1 to 100}';

    protected $description = 'Search one event collection using a local selfie without storing the image.';

    public function handle(FaceRecognitionService $service): int
    {
        $event = $this->findEvent((string) $this->argument('event'));

        if (! $event) {
            $this->error('Event not found.');

            return self::FAILURE;
        }

        if ($event->status !== Event::STATUS_PUBLISHED) {
            $this->error('The event must be published before face search.');

            return self::FAILURE;
        }

        $threshold = $this->threshold();
        $maxFaces = $this->maxFaces();
        $imagePath = (string) $this->argument('image');
        $imageBytes = $this->readImage($imagePath);

        if ($threshold === null || $maxFaces === null || $imageBytes === null) {
            return self::FAILURE;
        }

        try {
            $result = $service->searchEventByImage($event, $imageBytes, $threshold, $maxFaces);
        } catch (Throwable $exception) {
            $this->error('Face search failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Event: '.$event->title);
        $this->line('Collection: '.$result['collection_id']);
        $this->line('Searching selfie: '.basename($imagePath));
        $this->newLine();

        if (! $result['face_detected']) {
            $this->warn('No clear face was detected. Use one clear, front-facing selfie.');

            return self::SUCCESS;
        }

        $matches = $this->resolveMatches($event, $result['matches']);

        if ($matches === []) {
            $this->info('No Wivor photos matched.');

            return self::SUCCESS;
        }

        $this->table(['Similarity', 'Photo ID', 'Photo UUID'], array_map(
            fn (array $match): array => [
                number_format($match['similarity'], 2).'%',
                $match['photo']->id,
                $match['photo']->uuid,
            ],
            $matches,
        ));

        $count = count($matches);
        $this->info($count.' Wivor '.($count === 1 ? 'photo' : 'photos').' matched.');

        return self::SUCCESS;
    }

    private function findEvent(string $identifier): ?Event
    {
        return Event::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('uuid', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhereKey((int) $identifier);
                }
            })
            ->first();
    }

    private function threshold(): ?float
    {
        $value = $this->option('threshold');

        if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
            $this->error('The threshold must be a number from 0 to 100.');

            return null;
        }

        return (float) $value;
    }

    private function maxFaces(): ?int
    {
        $value = filter_var($this->option('max'), FILTER_VALIDATE_INT);

        if ($value === false || $value < 1 || $value > self::MAX_RESULTS) {
            $this->error('The maximum result count must be an integer from 1 to '.self::MAX_RESULTS.'.');

            return null;
        }

        return $value;
    }

    private function readImage(string $path): ?string
    {
        if (! is_file($path) || ! is_readable($path)) {
            $this->error('The selfie file does not exist or is not readable.');

            return null;
        }

        $size = filesize($path);

        if ($size === false || $size < 1 || $size > self::MAX_IMAGE_BYTES) {
            $this->error('The selfie must be larger than 0 bytes and no larger than 5 MB.');

            return null;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $this->error('The selfie must be a JPEG or PNG image.');

            return null;
        }

        $bytes = file_get_contents($path);

        if ($bytes === false) {
            $this->error('The selfie could not be read.');

            return null;
        }

        return $bytes;
    }

    /**
     * @param  list<array{photo_uuid: string, similarity: float}>  $candidates
     * @return list<array{photo: Photo, similarity: float}>
     */
    private function resolveMatches(Event $event, array $candidates): array
    {
        $similarities = [];

        foreach ($candidates as $candidate) {
            $photoUuid = $candidate['photo_uuid'];
            $similarities[$photoUuid] = max(
                $similarities[$photoUuid] ?? 0,
                $candidate['similarity'],
            );
        }

        $photos = $event->photos()
            ->where('status', Photo::STATUS_PUBLISHED)
            ->whereIn('uuid', array_keys($similarities))
            ->get()
            ->keyBy('uuid');
        $matches = [];

        foreach ($similarities as $photoUuid => $similarity) {
            if ($photo = $photos->get($photoUuid)) {
                $matches[] = ['photo' => $photo, 'similarity' => $similarity];
            }
        }

        usort($matches, fn (array $left, array $right): int => $right['similarity'] <=> $left['similarity']);

        return $matches;
    }
}

<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\PhotoBibNumber;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BibNumberDetector
{
    private RekognitionClient $client;

    public function __construct(?RekognitionClient $client = null)
    {
        $this->client = $client ?? new RekognitionClient($this->clientConfig());
    }

    /** @return list<array{bib_number: string, confidence: float}> */
    public function detect(Photo $photo): array
    {
        $disk = config('photo_uploads.disk');
        $bucket = config("filesystems.disks.{$disk}.bucket");

        if (blank($bucket)) {
            throw new RuntimeException("No S3 bucket is configured for the [{$disk}] media disk.");
        }

        $result = $this->client->detectText([
            'Image' => [
                'S3Object' => [
                    'Bucket' => $bucket,
                    'Name' => $photo->original_key,
                ],
            ],
        ]);

        $candidates = [];
        $minimumConfidence = (float) config('bib_recognition.min_confidence');

        foreach ($result->get('TextDetections') ?? [] as $detection) {
            $text = trim((string) ($detection['DetectedText'] ?? ''));
            $confidence = (float) ($detection['Confidence'] ?? 0);

            if ($confidence < $minimumConfidence || ! preg_match('/^(?!0+$)\d{1,5}$/', $text)) {
                continue;
            }

            $candidates[$text] = max($candidates[$text] ?? 0, $confidence);
        }

        ksort($candidates, SORT_NATURAL);

        return collect($candidates)
            ->map(fn (float $confidence, string $bibNumber): array => [
                'bib_number' => $bibNumber,
                'confidence' => round($confidence, 2),
            ])
            ->values()
            ->all();
    }

    /** @return list<array{bib_number: string, confidence: float}> */
    public function scanAndSave(Photo $photo): array
    {
        if ($photo->bib_scanned_at) {
            return $photo->bibNumbers()
                ->orderBy('bib_number')
                ->get(['bib_number', 'confidence'])
                ->map(fn (PhotoBibNumber $bib): array => [
                    'bib_number' => $bib->bib_number,
                    'confidence' => $bib->confidence,
                ])
                ->all();
        }

        $candidates = $this->detect($photo);

        DB::transaction(function () use ($photo, $candidates): void {
            $now = now();

            if ($candidates !== []) {
                PhotoBibNumber::upsert(
                    array_map(fn (array $candidate): array => [
                        'photo_id' => $photo->id,
                        'bib_number' => $candidate['bib_number'],
                        'confidence' => $candidate['confidence'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $candidates),
                    ['photo_id', 'bib_number'],
                    ['confidence', 'updated_at'],
                );
            }

            $photo->update(['bib_scanned_at' => $now]);
        });

        return $candidates;
    }

    /** @return array<string, mixed> */
    private function clientConfig(): array
    {
        $disk = config('photo_uploads.disk');
        $storage = config("filesystems.disks.{$disk}", []);
        $config = [
            'version' => 'latest',
            'region' => $storage['region'] ?? config('filesystems.disks.s3.region'),
        ];

        if (filled($storage['key'] ?? null) && filled($storage['secret'] ?? null)) {
            $config['credentials'] = array_filter([
                'key' => $storage['key'],
                'secret' => $storage['secret'],
                'token' => $storage['token'] ?? null,
            ]);
        }

        return $config;
    }
}

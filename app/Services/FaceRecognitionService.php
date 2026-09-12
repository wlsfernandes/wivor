<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Photo;
use Aws\Exception\AwsException;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Str;
use RuntimeException;

class FaceRecognitionService
{
    private const COLLECTION_PREFIX = 'wivor-event-';

    private RekognitionClient $client;

    public function __construct(?RekognitionClient $client = null)
    {
        $this->client = $client ?? new RekognitionClient($this->clientConfig());
    }

    public function collectionId(Event $event): string
    {
        $eventIdentifier = trim((string) preg_replace('/[^a-zA-Z0-9_.-]+/', '-', (string) $event->uuid), '.-');

        if ($eventIdentifier === '') {
            throw new RuntimeException('The event does not have a valid UUID for face indexing.');
        }

        return substr(self::COLLECTION_PREFIX.$eventIdentifier, 0, 255);
    }

    public function ensureEventCollection(Event $event): string
    {
        $collectionId = $this->collectionId($event);

        try {
            $this->client->describeCollection(['CollectionId' => $collectionId]);
        } catch (AwsException $exception) {
            if ($exception->getAwsErrorCode() !== 'ResourceNotFoundException') {
                throw $exception;
            }

            try {
                $this->client->createCollection(['CollectionId' => $collectionId]);
            } catch (AwsException $createException) {
                if ($createException->getAwsErrorCode() !== 'ResourceAlreadyExistsException') {
                    throw $createException;
                }
            }
        }

        return $collectionId;
    }

    /** @return array{collection_id: string, faces_indexed: int} */
    public function indexPhoto(Photo $photo): array
    {
        $event = $photo->event;

        if (! $event) {
            throw new RuntimeException('The photo does not belong to an event.');
        }

        if (blank($photo->original_key)) {
            throw new RuntimeException('The photo does not have an original S3 object key.');
        }

        $disk = config('photo_uploads.disk');
        $bucket = config("filesystems.disks.{$disk}.bucket");

        if (blank($bucket)) {
            throw new RuntimeException("No S3 bucket is configured for the [{$disk}] media disk.");
        }

        $collectionId = $this->ensureEventCollection($event);
        $result = $this->client->indexFaces([
            'CollectionId' => $collectionId,
            'Image' => [
                'S3Object' => [
                    'Bucket' => $bucket,
                    'Name' => $photo->original_key,
                ],
            ],
            'ExternalImageId' => $photo->uuid,
            'QualityFilter' => 'AUTO',
        ]);

        return [
            'collection_id' => $collectionId,
            'faces_indexed' => count($result->get('FaceRecords') ?? []),
        ];
    }

    /**
     * @return array{
     *     collection_id: string,
     *     face_detected: bool,
     *     matches: list<array{photo_uuid: string, similarity: float}>
     * }
     */
    public function searchEventByImage(Event $event, string $imageBytes, float $threshold, int $maxFaces): array
    {
        $collectionId = $this->collectionId($event);

        try {
            $result = $this->client->searchFacesByImage([
                'CollectionId' => $collectionId,
                'Image' => ['Bytes' => $imageBytes],
                'FaceMatchThreshold' => $threshold,
                'MaxFaces' => $maxFaces,
            ]);
        } catch (AwsException $exception) {
            if ($exception->getAwsErrorCode() === 'ResourceNotFoundException') {
                return [
                    'collection_id' => $collectionId,
                    'face_detected' => true,
                    'matches' => [],
                ];
            }

            if ($exception->getAwsErrorCode() === 'InvalidParameterException') {
                return [
                    'collection_id' => $collectionId,
                    'face_detected' => false,
                    'matches' => [],
                ];
            }

            throw $exception;
        }

        $similarities = [];

        foreach ($result->get('FaceMatches') ?? [] as $match) {
            $photoUuid = (string) data_get($match, 'Face.ExternalImageId');

            if (! Str::isUuid($photoUuid)) {
                continue;
            }

            $similarities[$photoUuid] = max(
                $similarities[$photoUuid] ?? 0,
                (float) ($match['Similarity'] ?? 0),
            );
        }

        arsort($similarities, SORT_NUMERIC);

        return [
            'collection_id' => $collectionId,
            'face_detected' => true,
            'matches' => collect($similarities)
                ->map(fn (float $similarity, string $photoUuid): array => [
                    'photo_uuid' => $photoUuid,
                    'similarity' => $similarity,
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array{collection_id: string, faces_deleted: int} */
    public function deletePhotoFaces(Photo $photo): array
    {
        $event = $photo->event;

        if (! $event) {
            throw new RuntimeException('The photo does not belong to an event.');
        }

        $collectionId = $this->collectionId($event);
        $faceIds = [];
        $nextToken = null;

        do {
            $parameters = [
                'CollectionId' => $collectionId,
                'MaxResults' => 4096,
            ];

            if ($nextToken) {
                $parameters['NextToken'] = $nextToken;
            }

            try {
                $result = $this->client->listFaces($parameters);
            } catch (AwsException $exception) {
                if ($exception->getAwsErrorCode() !== 'ResourceNotFoundException') {
                    throw $exception;
                }

                return ['collection_id' => $collectionId, 'faces_deleted' => 0];
            }

            foreach ($result->get('Faces') ?? [] as $face) {
                if (($face['ExternalImageId'] ?? null) === $photo->uuid && filled($face['FaceId'] ?? null)) {
                    $faceIds[(string) $face['FaceId']] = true;
                }
            }

            $nextToken = $result->get('NextToken');
        } while (filled($nextToken));

        if ($faceIds === []) {
            return ['collection_id' => $collectionId, 'faces_deleted' => 0];
        }

        $deletedCount = 0;

        foreach (array_chunk(array_keys($faceIds), 4096) as $chunk) {
            try {
                $result = $this->client->deleteFaces([
                    'CollectionId' => $collectionId,
                    'FaceIds' => $chunk,
                ]);
            } catch (AwsException $exception) {
                if ($exception->getAwsErrorCode() !== 'ResourceNotFoundException') {
                    throw $exception;
                }

                return ['collection_id' => $collectionId, 'faces_deleted' => $deletedCount];
            }

            foreach ($result->get('UnsuccessfulFaceDeletions') ?? [] as $failure) {
                $reasons = $failure['Reasons'] ?? [];

                if (array_diff($reasons, ['FACE_NOT_FOUND']) !== []) {
                    throw new RuntimeException('Amazon Rekognition could not delete every indexed face for the photo.');
                }
            }

            $deletedCount += count($result->get('DeletedFaces') ?? []);
        }

        return ['collection_id' => $collectionId, 'faces_deleted' => $deletedCount];
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

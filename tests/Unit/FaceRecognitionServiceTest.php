<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Photo;
use App\Services\FaceRecognitionService;
use Aws\Command;
use Aws\Exception\AwsException;
use Aws\MockHandler;
use Aws\Rekognition\RekognitionClient;
use Aws\Result;
use Tests\TestCase;

class FaceRecognitionServiceTest extends TestCase
{
    public function test_event_collection_identifier_is_deterministic_and_valid(): void
    {
        $event = new Event;
        $event->forceFill(['uuid' => 'A45CBA89-67BD-4E8B-9ADF-78FDC25C5F2B']);
        $service = new FaceRecognitionService($this->client(new MockHandler));

        $first = $service->collectionId($event);
        $second = $service->collectionId($event);

        $this->assertSame('wivor-event-A45CBA89-67BD-4E8B-9ADF-78FDC25C5F2B', $first);
        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_.-]{1,255}$/', $first);
    }

    public function test_missing_event_collection_is_created_idempotently(): void
    {
        $handler = new MockHandler([
            new AwsException('Collection missing.', new Command('DescribeCollection'), [
                'code' => 'ResourceNotFoundException',
            ]),
            new Result(['StatusCode' => 200]),
        ]);
        $event = $this->event();
        $service = new FaceRecognitionService($this->client($handler));

        $collectionId = $service->ensureEventCollection($event);

        $this->assertSame('wivor-event-'.$event->uuid, $collectionId);
        $this->assertSame('CreateCollection', $handler->getLastCommand()->getName());
        $this->assertSame($collectionId, $handler->getLastCommand()['CollectionId']);
        $this->assertCount(0, $handler);
    }

    public function test_collection_creation_race_is_treated_as_success(): void
    {
        $handler = new MockHandler([
            new AwsException('Collection missing.', new Command('DescribeCollection'), [
                'code' => 'ResourceNotFoundException',
            ]),
            new AwsException('Collection already exists.', new Command('CreateCollection'), [
                'code' => 'ResourceAlreadyExistsException',
            ]),
        ]);
        $event = $this->event();
        $service = new FaceRecognitionService($this->client($handler));

        $this->assertSame('wivor-event-'.$event->uuid, $service->ensureEventCollection($event));
        $this->assertCount(0, $handler);
    }

    public function test_indexing_uses_the_original_s3_object_and_accepts_multiple_faces(): void
    {
        $handler = new MockHandler([
            new Result(['FaceCount' => 0]),
            new Result(['FaceRecords' => [
                ['Face' => ['FaceId' => '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef']],
                ['Face' => ['FaceId' => '0d88238a-1bb2-411c-84d9-34ce39232815']],
            ]]),
        ]);
        [$event, $photo] = $this->photo();
        $service = new FaceRecognitionService($this->client($handler));

        $result = $service->indexPhoto($photo);
        $command = $handler->getLastCommand();

        $this->assertSame([
            'collection_id' => 'wivor-event-'.$event->uuid,
            'faces_indexed' => 2,
        ], $result);
        $this->assertSame('IndexFaces', $command->getName());
        $this->assertSame($photo->uuid, $command['ExternalImageId']);
        $this->assertSame('wivor-test-bucket', $command['Image']['S3Object']['Bucket']);
        $this->assertSame($photo->original_key, $command['Image']['S3Object']['Name']);
        $this->assertSame('AUTO', $command['QualityFilter']);
        $this->assertArrayNotHasKey('DetectionAttributes', $command->toArray());
    }

    public function test_repeated_indexing_uses_identical_aws_deduplication_keys(): void
    {
        $handler = new MockHandler([
            new Result(['FaceCount' => 0]),
            new Result(['FaceRecords' => [['Face' => ['FaceId' => '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef']]]]),
            new Result(['FaceCount' => 1]),
            new Result(['FaceRecords' => [['Face' => ['FaceId' => '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef']]]]),
        ]);
        [, $photo] = $this->photo();
        $service = new FaceRecognitionService($this->client($handler));

        $service->indexPhoto($photo);
        $first = $handler->getLastCommand()->toArray();
        $service->indexPhoto($photo);
        $second = $handler->getLastCommand()->toArray();

        $this->assertSame($first['CollectionId'], $second['CollectionId']);
        $this->assertSame($first['ExternalImageId'], $second['ExternalImageId']);
        $this->assertSame($first['Image'], $second['Image']);
        $this->assertCount(0, $handler);
    }

    public function test_search_sends_image_bytes_directly_and_uses_the_event_collection(): void
    {
        $photoUuid = 'cbbdb4a0-09d0-4c02-8241-c8e4f52a71ba';
        $handler = new MockHandler([
            new Result(['FaceMatches' => [[
                'Similarity' => 97.843,
                'Face' => ['ExternalImageId' => $photoUuid],
            ]]]),
        ]);
        $event = $this->event();
        $service = new FaceRecognitionService($this->client($handler));
        $imageBytes = 'local-selfie-bytes';

        $result = $service->searchEventByImage($event, $imageBytes, 90, 50);
        $command = $handler->getLastCommand();

        $this->assertSame('SearchFacesByImage', $command->getName());
        $this->assertSame('wivor-event-'.$event->uuid, $command['CollectionId']);
        $this->assertSame($imageBytes, $command['Image']['Bytes']);
        $this->assertArrayNotHasKey('S3Object', $command['Image']);
        $this->assertSame(90.0, $command['FaceMatchThreshold']);
        $this->assertSame(50, $command['MaxFaces']);
        $this->assertSame([
            'collection_id' => 'wivor-event-'.$event->uuid,
            'face_detected' => true,
            'matches' => [['photo_uuid' => $photoUuid, 'similarity' => 97.843]],
        ], $result);
    }

    public function test_search_deduplicates_photo_uuids_using_the_highest_similarity(): void
    {
        $photoUuid = 'cbbdb4a0-09d0-4c02-8241-c8e4f52a71ba';
        $handler = new MockHandler([
            new Result(['FaceMatches' => [
                ['Similarity' => 91.4, 'Face' => ['ExternalImageId' => $photoUuid]],
                ['Similarity' => 98.2, 'Face' => ['ExternalImageId' => $photoUuid]],
                ['Similarity' => 99.9, 'Face' => ['ExternalImageId' => 'not-a-photo-uuid']],
            ]]),
        ]);
        $service = new FaceRecognitionService($this->client($handler));

        $result = $service->searchEventByImage($this->event(), 'selfie', 85, 50);

        $this->assertSame([
            ['photo_uuid' => $photoUuid, 'similarity' => 98.2],
        ], $result['matches']);
    }

    public function test_search_returns_a_clean_no_face_result(): void
    {
        $handler = new MockHandler([
            new AwsException('No face detected.', new Command('SearchFacesByImage'), [
                'code' => 'InvalidParameterException',
            ]),
        ]);
        $event = $this->event();
        $service = new FaceRecognitionService($this->client($handler));

        $this->assertSame([
            'collection_id' => 'wivor-event-'.$event->uuid,
            'face_detected' => false,
            'matches' => [],
        ], $service->searchEventByImage($event, 'no-face-image', 90, 50));
    }

    public function test_search_treats_a_missing_event_collection_as_no_matches(): void
    {
        [$event] = $this->photo();
        $handler = new MockHandler([
            new AwsException('Collection missing.', new Command('SearchFacesByImage'), [
                'code' => 'ResourceNotFoundException',
            ]),
        ]);
        $service = new FaceRecognitionService($this->client($handler));

        $this->assertSame([
            'collection_id' => 'wivor-event-'.$event->uuid,
            'face_detected' => true,
            'matches' => [],
        ], $service->searchEventByImage($event, 'selfie-image', 90, 50));
    }

    public function test_cleanup_deletes_every_face_for_the_photo_across_collection_pages(): void
    {
        [, $photo] = $this->photo();
        $otherPhotoUuid = '3486598e-c67c-46bb-9d80-a7c9f60af8fa';
        $handler = new MockHandler([
            new Result([
                'Faces' => [
                    ['FaceId' => '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef', 'ExternalImageId' => $photo->uuid],
                    ['FaceId' => '49f85fc7-9df7-4734-bbbc-dabc7f0abff8', 'ExternalImageId' => $otherPhotoUuid],
                ],
                'NextToken' => 'next-page',
            ]),
            new Result(['Faces' => [
                ['FaceId' => '0d88238a-1bb2-411c-84d9-34ce39232815', 'ExternalImageId' => $photo->uuid],
            ]]),
            new Result(['DeletedFaces' => [
                '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef',
                '0d88238a-1bb2-411c-84d9-34ce39232815',
            ]]),
        ]);
        $service = new FaceRecognitionService($this->client($handler));

        $result = $service->deletePhotoFaces($photo);
        $command = $handler->getLastCommand();

        $this->assertSame([
            'collection_id' => 'wivor-event-'.$photo->event->uuid,
            'faces_deleted' => 2,
        ], $result);
        $this->assertSame('DeleteFaces', $command->getName());
        $this->assertSame('wivor-event-'.$photo->event->uuid, $command['CollectionId']);
        $this->assertSame([
            '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef',
            '0d88238a-1bb2-411c-84d9-34ce39232815',
        ], $command['FaceIds']);
        $this->assertNotContains('49f85fc7-9df7-4734-bbbc-dabc7f0abff8', $command['FaceIds']);
        $this->assertCount(0, $handler);
    }

    public function test_cleanup_with_no_matching_faces_is_a_success(): void
    {
        [, $photo] = $this->photo();
        $handler = new MockHandler([new Result(['Faces' => [[
            'FaceId' => '49f85fc7-9df7-4734-bbbc-dabc7f0abff8',
            'ExternalImageId' => '3486598e-c67c-46bb-9d80-a7c9f60af8fa',
        ]]])]);
        $service = new FaceRecognitionService($this->client($handler));

        $this->assertSame([
            'collection_id' => 'wivor-event-'.$photo->event->uuid,
            'faces_deleted' => 0,
        ], $service->deletePhotoFaces($photo));
        $this->assertSame('ListFaces', $handler->getLastCommand()->getName());
        $this->assertCount(0, $handler);
    }

    public function test_cleanup_can_run_repeatedly_without_failure(): void
    {
        [, $photo] = $this->photo();
        $handler = new MockHandler([
            new Result(['Faces' => [[
                'FaceId' => '94c8399a-cf5d-4d5e-b6a5-b9206512a7ef',
                'ExternalImageId' => $photo->uuid,
            ]]]),
            new Result(['DeletedFaces' => ['94c8399a-cf5d-4d5e-b6a5-b9206512a7ef']]),
            new Result(['Faces' => []]),
        ]);
        $service = new FaceRecognitionService($this->client($handler));

        $first = $service->deletePhotoFaces($photo);
        $second = $service->deletePhotoFaces($photo);

        $this->assertSame(1, $first['faces_deleted']);
        $this->assertSame(0, $second['faces_deleted']);
        $this->assertCount(0, $handler);
    }

    public function test_cleanup_treats_a_missing_event_collection_as_already_clean(): void
    {
        [, $photo] = $this->photo();
        $handler = new MockHandler([
            new AwsException('Collection missing.', new Command('ListFaces'), [
                'code' => 'ResourceNotFoundException',
            ]),
        ]);
        $service = new FaceRecognitionService($this->client($handler));

        $this->assertSame(0, $service->deletePhotoFaces($photo)['faces_deleted']);
        $this->assertCount(0, $handler);
    }

    private function client(MockHandler $handler): RekognitionClient
    {
        config([
            'photo_uploads.disk' => 's3',
            'filesystems.disks.s3.bucket' => 'wivor-test-bucket',
        ]);

        return new RekognitionClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => false,
            'handler' => $handler,
        ]);
    }

    private function event(): Event
    {
        $event = new Event;
        $event->forceFill(['uuid' => 'a45cba89-67bd-4e8b-9adf-78fdc25c5f2b']);

        return $event;
    }

    /** @return array{Event, Photo} */
    private function photo(): array
    {
        $event = $this->event();
        $photo = new Photo([
            'uuid' => 'eb3a53f1-f0fb-4201-9f61-a9f1e8d0ed30',
            'original_key' => 'events/test/photos/one/original.jpg',
        ]);
        $photo->setRelation('event', $event);

        return [$event, $photo];
    }
}

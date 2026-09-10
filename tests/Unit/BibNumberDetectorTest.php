<?php

namespace Tests\Unit;

use App\Models\Photo;
use App\Services\BibNumberDetector;
use Aws\MockHandler;
use Aws\Rekognition\RekognitionClient;
use Aws\Result;
use Tests\TestCase;

class BibNumberDetectorTest extends TestCase
{
    public function test_it_sends_the_s3_object_to_rekognition_and_keeps_simple_numeric_candidates(): void
    {
        config([
            'photo_uploads.disk' => 's3',
            'filesystems.disks.s3.bucket' => 'wivor-test-bucket',
            'bib_recognition.min_confidence' => 80,
        ]);

        $handler = new MockHandler([
            new Result(['TextDetections' => [
                ['DetectedText' => 'RUN FAST 347 ATLANTA', 'Confidence' => 99, 'Type' => 'LINE'],
                ['DetectedText' => '347', 'Confidence' => 98.765, 'Type' => 'WORD'],
                ['DetectedText' => '347', 'Confidence' => 95, 'Type' => 'LINE'],
                ['DetectedText' => '0042', 'Confidence' => 91, 'Type' => 'WORD'],
                ['DetectedText' => '0', 'Confidence' => 99, 'Type' => 'WORD'],
                ['DetectedText' => '123456', 'Confidence' => 99, 'Type' => 'WORD'],
                ['DetectedText' => '555', 'Confidence' => 79.99, 'Type' => 'WORD'],
                ['DetectedText' => 'FINISH', 'Confidence' => 99, 'Type' => 'WORD'],
            ]]),
        ]);
        $client = new RekognitionClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => false,
            'handler' => $handler,
        ]);
        $photo = new Photo(['original_key' => 'events/race/photos/one/original.jpg']);

        $result = (new BibNumberDetector($client))->detect($photo);

        $this->assertSame([
            ['bib_number' => '0042', 'confidence' => 91.0],
            ['bib_number' => '347', 'confidence' => 98.77],
        ], $result);
        $this->assertSame('DetectText', $handler->getLastCommand()->getName());
        $this->assertSame('wivor-test-bucket', $handler->getLastCommand()['Image']['S3Object']['Bucket']);
        $this->assertSame($photo->original_key, $handler->getLastCommand()['Image']['S3Object']['Name']);
    }
}

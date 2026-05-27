<?php

use PHPUnit\Framework\Attributes\DataProvider;
use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\SourceImageCollection;

class SourceImageTest extends PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public static function createFromJsonDataProvider()
    {
        $imageReverser = static function (SourceImage $image) {
            $data = [
                'organization' => $image->organization,
                'binary_hash' => $image->binaryHash,
                'short_hash' => $image->shortHash,
                'hash' => $image->hash,
                'name' => $image->name,
                'format' => $image->format,
                'mimetype' => $image->mimetype,
                'size' => $image->size,
                'width' => $image->width,
                'height' => $image->height,
                'user_metadata' => $image->userMetadata,
                'dynamic_metadata' => [],
                'created' => $image->created->format("Y-m-d\TH:i:s.uP"),
                'link' => $image->link,
                'protected' => $image->protected,
                'locked' => $image->locked,
            ];

            foreach ($image->dynamicMetadata as $name => $meta) {
                $metaAsArray = [];
                if ($meta instanceof SubjectArea) {
                    $metaAsArray = ['width' => $meta->width, 'height' => $meta->height, 'x' => $meta->x, 'y' => $meta->y];
                }
                $data['dynamic_metadata'][$name] = $metaAsArray;
            }

            return $data;
        };

        $testData = [];

        $image = new SourceImage('organization', 'binaryHash', 'verylonghash', 'name', 'format', 'mimetype', 'size', 'width', 'height', [], [], [], new DateTime(), 'link', 'shorthash');
        $testData['base-image'] = [
            $image, $imageReverser($image), true,
        ];

        $image = new SourceImage('organization', 'binaryHash', 'verylonghash', 'name', 'format', 'mimetype', 'size', 'width', 'height', [], [], [], new DateTime(), 'link', 'shorthash');
        $testData['base-image-json'] = [
            $image, json_encode($imageReverser($image)),
        ];

        $subjectAres = new SubjectArea(10, 10, 100, 100);
        $image = new SourceImage('organization', 'binaryHash', 'verylonghash', 'name', 'format', 'mimetype', 'size', 'width', 'height', [], ['subject_area' => $subjectAres], [], new DateTime(), 'link', 'shorthash');
        $testData['image-subject-area'] = [
            $image, $imageReverser($image), true,
        ];

        $subjectAres = new SubjectArea(10, 10, 100, 100);
        $image = new SourceImage('organization', 'binaryHash', 'verylonghash', 'name', 'format', 'mimetype', 'size', 'width', 'height', [], ['subject_area' => $subjectAres], [], new DateTime(), 'link', 'shorthash');
        $testData['image-json-subject-area'] = [
            $image, json_encode($imageReverser($image)),
        ];

        return $testData;
    }

    /**
     * @param bool $isArray
     */
    #[DataProvider('createFromJsonDataProvider')]
    public function testCreateFromJson($expected, $data, $isArray = false)
    {
        if ($isArray) {
            $sourceImage = SourceImage::createFromDecodedJsonResponse($data);
        } else {
            $sourceImage = SourceImage::createFromJsonResponse($data);
        }
        $this->assertEquals($expected, $sourceImage);
    }

    /**
     * @param bool $isArray
     */
    #[DataProvider('createFromJsonDataProvider')]
    public function testCollectionCreateFromJson($expected, $data, $isArray = false)
    {
        if ($isArray) {
            $data = json_encode($data);
        }
        $json = '{"items": ['.$data.'], "offset": 0}';
        $sourceImages = SourceImageCollection::createFromJsonResponse($json);
        $this->assertEquals($expected, $sourceImages->current());
    }

    public function testCreateFromDecodedJsonWithUserMetadataDate(): void
    {
        $data = [
            'organization' => 'org',
            'binary_hash' => 'binhash',
            'short_hash' => 'shorthash',
            'hash' => 'longhash',
            'name' => 'test.jpg',
            'format' => 'jpg',
            'mimetype' => 'image/jpeg',
            'size' => 1024,
            'width' => 800,
            'height' => 600,
            'user_metadata' => ['date:taken' => '2024-01-15T10:30:00+00:00'],
            'dynamic_metadata' => [],
            'static_metadata' => [],
            'created' => '2024-01-15T10:30:00.000000+00:00',
            'link' => '/sourceimages/org/longhash',
            'protected' => false,
            'locked' => false,
        ];

        $image = SourceImage::createFromDecodedJsonResponse($data);
        $this->assertInstanceOf(\DateTime::class, $image->userMetadata['date:taken']);
    }

    public function testCreateFromDecodedJsonWithMissingUserMetadata(): void
    {
        $data = [
            'organization' => 'org',
            'binary_hash' => 'binhash',
            'short_hash' => 'shorthash',
            'hash' => 'longhash',
            'name' => 'test.jpg',
            'format' => 'jpg',
            'mimetype' => 'image/jpeg',
            'size' => 1024,
            'width' => 800,
            'height' => 600,
            'dynamic_metadata' => [],
            'created' => '2024-01-15T10:30:00.000000+00:00',
            'link' => '/sourceimages/org/longhash',
            'protected' => false,
            'locked' => false,
        ];

        $image = SourceImage::createFromDecodedJsonResponse($data);
        $this->assertSame([], $image->userMetadata);
        $this->assertSame([], $image->staticMetadata);
    }

    public function testSourceImageShortHashDefaultsToHash(): void
    {
        $image = new SourceImage('org', 'binhash', 'myhash', 'name', 'jpg', 'image/jpeg', 100, 10, 10, [], [], [], new DateTime(), 'link');
        $this->assertSame('myhash', $image->shortHash);
    }

    public function testSourceImageProtectedAndLocked(): void
    {
        $image = new SourceImage('org', 'binhash', 'myhash', 'name', 'jpg', 'image/jpeg', 100, 10, 10, [], [], [], new DateTime(), 'link', 'short', true, true);
        $this->assertTrue($image->protected);
        $this->assertTrue($image->locked);
    }

    public function testCollectionTotalAndCursor(): void
    {
        $json = json_encode([
            'items' => [],
            'total' => 42,
            'cursor' => 'abc123',
            'links' => ['next' => ['href' => '/sourceimages?offset=10']],
        ]);

        $collection = SourceImageCollection::createFromJsonResponse($json);
        $this->assertSame(42, $collection->getTotal());
        $this->assertSame('abc123', $collection->getCursor());
        $this->assertSame(['next' => ['href' => '/sourceimages?offset=10']], $collection->getLinks());
        $this->assertCount(0, $collection);
    }

    public function testCollectionIterator(): void
    {
        $data = [
            'organization' => 'org',
            'binary_hash' => 'binhash',
            'short_hash' => 'short',
            'hash' => 'longhash',
            'name' => 'test.jpg',
            'format' => 'jpg',
            'mimetype' => 'image/jpeg',
            'size' => 1024,
            'width' => 800,
            'height' => 600,
            'user_metadata' => [],
            'dynamic_metadata' => [],
            'static_metadata' => [],
            'created' => '2024-01-15T10:30:00.000000+00:00',
            'link' => '/sourceimages/org/longhash',
            'protected' => false,
            'locked' => false,
        ];

        $json = json_encode([
            'items' => [$data, $data],
            'total' => 2,
        ]);

        $collection = SourceImageCollection::createFromJsonResponse($json);
        $this->assertCount(2, $collection);
        $this->assertSame(2, $collection->getTotal());

        // Test iterator
        $count = 0;
        foreach ($collection as $key => $image) {
            $this->assertInstanceOf(SourceImage::class, $image);
            $this->assertSame($count, $key);
            ++$count;
        }
        $this->assertSame(2, $count);

        // Test rewind
        $collection->rewind();
        $this->assertTrue($collection->valid());
        $this->assertSame(0, $collection->key());
    }

    public function testCollectionMissingTotalDefaults(): void
    {
        $json = json_encode(['items' => []]);
        $collection = SourceImageCollection::createFromJsonResponse($json);
        $this->assertSame(0, $collection->getTotal());
        $this->assertNull($collection->getCursor());
        $this->assertSame([], $collection->getLinks());
    }
}

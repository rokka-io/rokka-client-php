<?php

use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\SourceImageCollection;

class SourceImageTest extends PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function createFromJsonDataProvider()
    {
        $imageReverser = function (SourceImage $image) {
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
     * @dataProvider createFromJsonDataProvider
     *
     * @param bool $isArray
     */
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
     * @dataProvider createFromJsonDataProvider
     *
     * @param bool $isArray
     */
    public function testCollectionCreateFromJson($expected, $data, $isArray = false)
    {
        if ($isArray) {
            $data = json_encode($data);
        }
        $json = '{"items": ['.$data.'], "offset": 0}';
        $sourceImages = SourceImageCollection::createFromJsonResponse($json);
        $this->assertEquals($expected, $sourceImages->current());
    }
}

<?php

use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\SourceImage;

class SourceImageTest extends \PHPUnit_Framework_TestCase
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
                'hash' => $image->hash,
                'name' => $image->name,
                'format' => $image->format,
                'size' => $image->size,
                'width' => $image->width,
                'height' => $image->height,
                'user_metadata' => $image->userMetadata,
                'dynamic_metadata' => [],
                'created' => $image->created->format(DateTime::RFC3339),
                'link' => $image->link,
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

        $image = new SourceImage('organization', 'binaryHash', 'hash', 'name', 'format', 'size', 'width', 'height', [], [], new DateTime(), 'link');
        $testData['base-image'] = [
            $image, $imageReverser($image), true,
        ];

        $image = new SourceImage('organization', 'binaryHash', 'hash', 'name', 'format', 'size', 'width', 'height', [], [], new DateTime(), 'link');
        $testData['base-image-json'] = [
            $image, json_encode($imageReverser($image)),
        ];

        $subjectAres = new SubjectArea(10, 10, 100, 100);
        $image = new SourceImage('organization', 'binaryHash', 'hash', 'name', 'format', 'size', 'width', 'height', [], ['subject_area' => $subjectAres], new DateTime(), 'link');
        $testData['image-subject-area'] = [
            $image, $imageReverser($image), true,
        ];

        $subjectAres = new SubjectArea(10, 10, 100, 100);
        $image = new SourceImage('organization', 'binaryHash', 'hash', 'name', 'format', 'size', 'width', 'height', [], ['subject_area' => $subjectAres], new DateTime(), 'link');
        $testData['image-json-subject-area'] = [
            $image, json_encode($imageReverser($image)),
        ];

        return $testData;
    }

    /**
     * @dataProvider createFromJsonDataProvider
     *
     * @param $expected
     * @param $data
     * @param bool $isArray
     */
    public function testCreateFromJson($expected, $data, $isArray = false)
    {
        $sourceImage = SourceImage::createFromJsonResponse($data, $isArray);
        $this->assertEquals($expected, $sourceImage);
    }

    /**
     * @return array
     */
    public function provideGetDynamicMetadataClassNameData()
    {
        return [
            ['Rokka\Client\Core\DynamicMetadata\A', 'a'],
            ['Rokka\Client\Core\DynamicMetadata\SubjectArea', 'subject_area'],
            ['Rokka\Client\Core\DynamicMetadata\SomeOtherFancyDynamicMetadata', 'some_other_fancy_dynamic_metadata'],
        ];
    }

    /**
     * @dataProvider provideGetDynamicMetadataClassNameData
     *
     * @param $expected
     * @param $name
     */
    public function testGetDynamicMetadataClassName($expected, $name)
    {
        $this->assertEquals($expected, SourceImage::getDynamicMetadataClassName($name));
    }
}

<?php

namespace Rokka\Client\Tests;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\DynamicMetadataHelper;

class DynamicMetadataHelperTest extends \PHPUnit_Framework_TestCase
{
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
        $this->assertEquals($expected, DynamicMetadataHelper::getDynamicMetadataClassName($name));
    }

    /**
     * @return array
     */
    public function provideBuildDynamicMetadataData()
    {
        return [
            // SubjectArea, name as snake_case
            [
                new SubjectArea(1, 1, 1, 1),
                'subject_area',
                ['x' => 1, 'y' => 1, 'width' => 1, 'height' => 1],
            ],
            // SubjectArea, class name
            [
                new SubjectArea(1, 1, 1, 1),
                'SubjectArea',
                ['x' => 1, 'y' => 1, 'width' => 1, 'height' => 1],
            ],
            // SubjectArea, with defaults
            [
                new SubjectArea(1, 1, 1, 1),
                'subject_area',
                ['x' => 1, 'y' => 1],
            ],
        ];
    }

    /**
     * @dataProvider provideBuildDynamicMetadataData
     *
     * @param DynamicMetadataInterface $excepted
     * @param string                   $name
     * @param array                    $data
     */
    public function testBuildDynamicMetadata($excepted, $name, $data)
    {
        $this->assertEquals($excepted, DynamicMetadataHelper::buildDynamicMetadata($name, $data));
    }
}

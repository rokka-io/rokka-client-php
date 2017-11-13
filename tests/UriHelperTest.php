<?php

namespace Rokka\Client\Tests;

use Rokka\Client\Core\Operation;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackOperation;
use Rokka\Client\UriHelper;

class UriHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provideAddOptionsToUri()
    {
        return [
            ['https://test.rokka.io/stackname/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/stackname/options-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/stackname/b53763.jpg', 'options-dpr-2', 'https://test.rokka.io/stackname/options-dpr-2/b53763.jpg'],
            ['https://test.rokka.io/stackname/resize-width-100/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/stackname/resize-width-100--options-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true/b53763.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b53763.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6/seo.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6/seo.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-3/b53763/seo.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b53763/seo.jpg'],
        ];
    }

    /**
     * @dataProvider provideAddOptionsToUri
     *
     * @param $inputUrl
     * @param $options
     * @param $expected
     */
    public function testAddOptionsToUri($inputUrl, $options, $expected)
    {
        $this->assertSame($expected, UriHelper::addOptionsToUriString($inputUrl, $options));
    }

    public function testGetDynamicStackFromStackObject() {
        $stack = new Stack(null, 'teststack');

        $stack->addStackOperation(new StackOperation('resize', ['width' => 200, 'height' => 200]));
        $stack->addStackOperation(new StackOperation('rotate', ['angle' => 45]));
        $stack->setStackOptions(['jpg.quality' => 80]);
        $stack->addStackOption('webp.quality', 80);
        $this->assertEquals('resize-height-200-width-200--rotate-angle-45--options-jpg.quality-80-webp.quality-80', UriHelper::getDynamicStackFromStackObject($stack));

    }
}

<?php

namespace Rokka\Client\Tests;

use GuzzleHttp\Psr7\Uri;
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
     * @return array
     */
    public function provideGetSrcSetUrl()
    {
        return [
            ['2x', null, 'https://test.rokka.io/stackname/options-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['3x', null, 'https://test.rokka.io/stackname/options-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', null, 'https://test.rokka.io/stackname/resize-width-300/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', '2x', 'https://test.rokka.io/stackname/resize-width-150--options-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', '3x', 'https://test.rokka.io/stackname/resize-width-100--options-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['2x', 'options-jpg.quality-50', 'https://test.rokka.io/stackname/options-dpr-2-jpg.quality-50/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', 'options-jpg.quality-50', 'https://test.rokka.io/stackname/resize-width-300--options-jpg.quality-50/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', 'options-jpg.quality-50-dpr-2', 'https://test.rokka.io/stackname/resize-width-150--options-dpr-2-jpg.quality-50/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', 'options-jpg.quality-50-dpr-2--resize-width-200', 'https://test.rokka.io/stackname/resize-width-200--options-dpr-2-jpg.quality-50/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['300w', 'options-jpg.quality-50--resize-width-200', 'https://test.rokka.io/stackname/resize-width-200--options-jpg.quality-50/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
        ];
    }

    /**
     * @return array
     */
    public function provideDecomposeUri()
    {
        return [
            ['https://test.rokka.io/stackname/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', ['stack' => 'stackname', 'hash' => 'b537639e539efcc3df4459ef87c5963aa5079ca6', 'filename' => null, 'format' => 'jpg']],
            ['https://test.rokka.io/stackname/resize-width-100/b537639e5.jpg', ['stack' => 'stackname', 'hash' => 'b537639e5', 'filename' => null, 'format' => 'jpg', 'operations' => ['resize' => ['width' => '100']]]],
            ['https://test.rokka.io/stackname/resize-width-100--rotate-angle-20/b537639e5.jpg', ['stack' => 'stackname', 'hash' => 'b537639e5', 'filename' => null, 'format' => 'jpg', 'operations' => ['resize' => ['width' => '100'], 'rotate' => ['angle' => '20']]]],
            ['https://test.rokka.io/dynamic/resize-height-200-width-100--options-jpg.quality-80/b53763/seo-test.webp', ['stack' => 'dynamic', 'hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',  'operations' => ['resize' => ['height' => '200', 'width' => '100']], 'options' => ['jpg.quality' => '80']]],
            ['https://test.rokka.io/dynamic/options-autoformat-true/b53763/seo-test.webp', ['stack' => 'dynamic', 'hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp', 'options' => ['autoformat' => 'true']]],
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

    public function testGetDynamicStackFromStackObject()
    {
        $stack = new Stack(null, 'teststack');

        $stack->addStackOperation(new StackOperation('resize', ['width' => 200, 'height' => 200]));
        $stack->addStackOperation(new StackOperation('rotate', ['angle' => 45]));
        $stack->setStackOptions(['jpg.quality' => 80]);
        $stack->addStackOption('webp.quality', 80);
        $this->assertEquals('dynamic/resize-height-200-width-200--rotate-angle-45--options-jpg.quality-80-webp.quality-80', $stack->getDynamicStackUrl());
    }

    /**
     * @dataProvider provideDecomposeUri
     *
     * @param string $inputUrl
     * @param array  $expected
     */
    public function testDecomposeUri($inputUrl, $expected)
    {
        $uri = new Uri($inputUrl);
        $components = UriHelper::decomposeUri($uri);
        $this->assertSame($expected, $components);
        $this->assertSame((string) UriHelper::composeUri($components, $uri), $inputUrl);
    }

    /**
     * @dataProvider provideGetSrcSetUrl
     *
     * @param $size
     * @param $custom
     * @param $expected
     */
    public function testGetSrcSetUrl($size, $custom, $expected)
    {
        $inputUrl = 'https://test.rokka.io/stackname/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg';
        $this->assertSame($expected, (string) UriHelper::getSrcSetUrlString($inputUrl, $size, $custom));
    }
}

<?php

namespace Rokka\Client\Tests;

use GuzzleHttp\Psr7\Uri;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackOperation;
use Rokka\Client\Core\StackUri;
use Rokka\Client\UriHelper;

class UriHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provideAddOptionsToUri()
    {
        return [
            ['', 'options-dpr-2', 'options-dpr-2'],
            ['', ['options' => ['dpr' => 2]], 'options-dpr-2'],
            ['resize-width-100', ['options' => ['dpr' => 2]], 'resize-width-100--options-dpr-2'],
            ['resize-width-100', ['options' => ['dpr' => 2], 'operations' => [new StackOperation('resize', ['width' => 200])]], 'resize-width-200--options-dpr-2'],
            ['resize-width-100', ['options' => ['dpr' => 2], 'operations' => [['name' => 'resize', 'options' => ['width' => 200]]]], 'resize-width-200--options-dpr-2'],

            ['resize-width-100/options-dpr-3', 'options-dpr-2', 'resize-width-100--options-dpr-2'],
            ['resize-width-100/options-dpr-3', ['options' => ['dpr' => 2]], 'resize-width-100--options-dpr-2'],

            ['resize-width-100/options-dpr-3', '', 'resize-width-100--options-dpr-3'],

            ['resize-width-100--resize-width-200', 'resize-width-300', 'resize-width-300--resize-width-300'],
            ['resize-width-100/resize-width-200', 'resize-width-300', 'resize-width-300'],
            ['resize-width-100', 'options-dpr-2--resize-width-200', 'resize-width-200--options-dpr-2'],
            ['resize-width-100--resize-width-200', 'options-dpr-2', 'resize-width-100--resize-width-200--options-dpr-2'],
            ['resize-width-100--options-autoformat-true-dpr-3', 'options-dpr-2', 'resize-width-100--options-autoformat-true-dpr-2'],
            ['resize-width-100--resize-width-300--options-autoformat-true-dpr-3', 'resize-height-200', 'resize-height-200-width-100--resize-height-200-width-300--options-autoformat-true-dpr-3'],
            ['resize-width-100--resize-width-300--options-autoformat-true-dpr-3', 'resize-height-200-width-200', 'resize-height-200-width-200--resize-height-200-width-200--options-autoformat-true-dpr-3'],
            ['resize-width-100--options-autoformat-true', 'options-dpr-2', 'resize-width-100--options-autoformat-true-dpr-2'],
            ['resize-width-100--options-autoformat-true-dpr-3', 'options-dpr-2', 'resize-width-100--options-autoformat-true-dpr-2'],
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

    public function provideDecomposeUri()
    {
        return [
            ['', ['optionsUrl' => '', 'optionsArray' => []]],
            ['/', ['optionsUrl' => '', 'optionsArray' => []]],
            ['options-jpg.quality-90/', ['optionsUrl' => 'options-jpg.quality-90', 'optionsArray' => ['jpg.quality' => '90']]],
            ['options-jpg.quality-90/options-dpr-2/', ['optionsUrl' => 'options-dpr-2-jpg.quality-90', 'optionsArray' => ['jpg.quality' => '90', 'dpr' => '2']]],
            ['options-jpg.quality-90/options-jpg.quality-40/', ['optionsUrl' => 'options-jpg.quality-40', 'optionsArray' => ['jpg.quality' => '40']]],
            ['options-jpg.quality-90--options-jpg.quality-40/', ['optionsUrl' => 'options-jpg.quality-40', 'optionsArray' => ['jpg.quality' => '40']]],
            ['resize-width-100//', ['optionsUrl' => 'resize-width-100', 'optionsArray' => []]],
            ['//options-jpg.quality-90//', ['optionsUrl' => 'options-jpg.quality-90', 'optionsArray' => ['jpg.quality' => '90']]],
            ['//options-jpg.quality-90/', ['optionsUrl' => 'options-jpg.quality-90', 'optionsArray' => ['jpg.quality' => '90']]],
            ['--options/', ['optionsUrl' => '', 'optionsArray' => []]],
            ['options--/', ['optionsUrl' => '', 'optionsArray' => []]],
            ['--/', ['optionsUrl' => '', 'optionsArray' => []]],
            ['resize-width-100--options-dpr-2/', ['optionsUrl' => 'resize-width-100--options-dpr-2', 'optionsArray' => ['dpr' => '2']]],
            ['resize-width-100--blur--resize-width-200--options-dpr-2/', ['optionsUrl' => 'resize-width-100--blur--resize-width-200--options-dpr-2', 'optionsArray' => ['dpr' => '2']]],
            ['resize-width-100--blur--resize-width-200--options-dpr-2/resize-width-300/', ['optionsUrl' => 'resize-width-300--blur--resize-width-300--options-dpr-2', 'optionsArray' => ['dpr' => 2]]],
        ];
    }

    /**
     * @dataProvider provideDecomposeUri
     *
     * @param $option
     * @param $expectedOptions
     */
    public function testDecomposeUri($option, $expectedOptions)
    {
        $stacks = [
            'noop',
            'dynamic',
            '5e05e0',
        ];

        $hashes = [
            'b537639e539efcc3df4459ef87c5963aa5079ca6',
            'b53763',
            '-some/remot-e/path.jpeg-',
            '-remote/image.jpg%3Fwidth=100-',
            '-some/rem-ote/image-',
        ];

        foreach ($stacks as $stack) {
            $this->twoRoutesTest($hashes, $stack, $option, $expectedOptions);
        }
    }

    /**
     * @dataProvider provideAddOptionsToUri
     *
     * @param string       $inputUrl
     * @param string|array $options
     * @param string       $expected
     */
    public function testAddOptionsToUri($inputUrl, $options, $expected)
    {
        $this->assertSame('https://test.rokka.io/stackname/'.$expected.'/b53763.jpg', UriHelper::addOptionsToUriString('https://test.rokka.io/stackname/'.$inputUrl.'/b53763.jpg', $options));
    }

    public function testGetDynamicStackFromStackObject()
    {
        $stack = new Stack(null, 'dynamic');

        $stack->addStackOperation(new StackOperation('resize', ['width' => 200, 'height' => 200]));
        $stack->addStackOperation(new StackOperation('rotate', ['angle' => 45]));
        $stack->setStackOptions(['jpg.quality' => 80]);
        $stack->addStackOption('webp.quality', 80);
        $this->assertEquals('dynamic/resize-height-200-width-200--rotate-angle-45--options-jpg.quality-80-webp.quality-80', $stack->getDynamicUriString());
    }

    /**
     * @dataProvider provideGetSrcSetUrl
     *
     * @param string      $size
     * @param string|null $custom
     * @param string      $expected
     */
    public function testGetSrcSetUrl($size, $custom, $expected)
    {
        $inputUrl = 'https://test.rokka.io/stackname/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg';
        $this->assertSame($expected, (string) UriHelper::getSrcSetUrlString($size, $inputUrl, $custom));
    }

    private function twoRoutesTest(array $hashes, $stack, $option, array $expectedOptions)
    {
        foreach ($hashes as $hash) {
            $this->singleRouteTest($stack.'/'.$option.$hash.'.jpeg', $hash, $expectedOptions, '', $stack);
            $this->singleRouteTest($stack.'/'.$option.$hash.'/5e05d0.jpeg', $hash, $expectedOptions, '5e05d0', $stack);
        }

        return $hash;
    }

    private function singleRouteTest($testUrl, $hash, array $expectedOptions, $filename, $stack)
    {
        $testUrl = 'https://test.rokka.io/'.$testUrl;
        $testUri = new Uri($testUrl);

        try {
            $components = UriHelper::decomposeUri($testUri);
        } catch (\InvalidArgumentException $e) {
            $this->fail('Arguments were not valid for url '.$testUrl.'.'.$e->getMessage());
        }
        /** @var StackUri $cStack */
        $cStack = $components['stack'];
        $expectedStackUri = trim($cStack->getName().'/'.$expectedOptions['optionsUrl'], '/');
        $this->assertEquals($expectedStackUri, $cStack->getStackUriString(), 'getStackUri was not as expected for url '.$testUrl);
        $this->assertEquals($hash, $components['hash'], 'Hash was not expected for url '.$testUrl);
        $this->assertEquals($filename, (string) $components['filename'], 'Filename was not as expected for url '.$testUrl);
        $this->assertEquals('jpeg', $components['format'], 'Format was not as expected for url '.$testUrl);
        $this->assertEquals($expectedOptions['optionsArray'], $cStack->getStackOptions(), 'Options were not as expected for url '.$testUrl);
        //test back with composeUri
        if ($filename) {
            $filename = '/'.$filename;
        }
        if ($expectedOptions['optionsUrl']) {
            $expectedOptions['optionsUrl'] = $expectedOptions['optionsUrl'].'/';
        }
        $this->assertEquals('https://test.rokka.io/'.$stack.'/'.$expectedOptions['optionsUrl'].$hash.$filename.'.jpeg', (string) UriHelper::composeUri($components, $testUri));
    }
}

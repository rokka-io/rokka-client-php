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
    public function provideDecomposeUri()
    {
        return [
            ['https://test.rokka.io/stackname/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg',
                ['hash' => 'b537639e539efcc3df4459ef87c5963aa5079ca6', 'filename' => null, 'format' => 'jpg',
                    'stackurl' => 'stackname', 'stackoptions' => [], ],
            ],
            ['https://test.rokka.io/stackname/resize-width-100/b537639e5.jpg',
                ['hash' => 'b537639e5', 'filename' => null, 'format' => 'jpg',
                    'stackurl' => 'stackname/resize-width-100', 'stackoptions' => [], ],
            ],
            ['https://test.rokka.io/stackname/resize-width-100--rotate-angle-20/b537639e5.jpg',
                ['hash' => 'b537639e5', 'filename' => null, 'format' => 'jpg',
                    'stackurl' => 'stackname/resize-width-100--rotate-angle-20', 'stackoptions' => [], ],
            ],
            ['https://test.rokka.io/dynamic/resize-height-200-width-100--options-jpg.quality-80/b53763/seo-test.webp',
                ['hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',
                    'stackurl' => 'dynamic/resize-height-200-width-100--options-jpg.quality-80', 'stackoptions' => ['jpg.quality' => '80'], ],
            ],
            ['https://test.rokka.io/dynamic/options-autoformat-true/b53763/seo-test.webp',
                ['hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',
                    'stackurl' => 'dynamic/options-autoformat-true', 'stackoptions' => ['autoformat' => 'true'], ],
            ],
            ['https://test.rokka.io/dynamic/options-autoformat-true/options-dpr-2/b53763/seo-test.webp',
                ['hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',
                    'stackurl' => 'dynamic/options-autoformat-true-dpr-2',  'stackoptions' => ['autoformat' => 'true', 'dpr' => '2'], ],
                'https://test.rokka.io/dynamic/options-autoformat-true-dpr-2/b53763/seo-test.webp',
            ],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true/resize-width-200/b53763/seo-test.webp',
                ['hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',
                    'stackurl' => 'dynamic/resize-width-200--options-autoformat-true', 'stackoptions' => ['autoformat' => 'true'], ],
                'https://test.rokka.io/dynamic/resize-width-200--options-autoformat-true/b53763/seo-test.webp',
            ],
            ['https://test.rokka.io/dynamic/resize-width-100--resize-width-300--options-autoformat-true/resize-width-200/b53763/seo-test.webp',
                ['hash' => 'b53763', 'filename' => 'seo-test', 'format' => 'webp',
                    'stackurl' => 'dynamic/resize-width-200--resize-width-200--options-autoformat-true', 'stackoptions' => ['autoformat' => 'true'], ],
                'https://test.rokka.io/dynamic/resize-width-200--resize-width-200--options-autoformat-true/b53763/seo-test.webp',
            ],
        ];
    }


    /**
     * @dataProvider provideDecomposeUri
     *
     * @param string      $inputUrl
     * @param array       $expected
     * @param string|null $expectedComposeUrl
     */
    public function testDecomposeUri($inputUrl, $expected, $expectedComposeUrl = null)
    {
        $uri = new Uri($inputUrl);
        $components = UriHelper::decomposeUri($uri);
        $stack = $components->getStack();
        $this->assertEquals($expected['stackoptions'], $stack->getStackOptions());
        $this->assertEquals($expected['stackurl'], $stack->getStackUri());
        $this->assertEquals($expected['hash'], $components->getHash());
        $this->assertEquals($expected['filename'], $components->getFilename());
        $this->assertEquals($expected['format'], $components->getFormat());
        if (null === $expectedComposeUrl) {
            $expectedComposeUrl = $inputUrl;
        }
        $this->assertSame((string) UriHelper::composeUri($components, $uri), $expectedComposeUrl);
    }


    public function testGetDynamicStackFromStackObject()
    {
        $stack = new Stack(null, 'dynamic');

        $stack->addStackOperation(new StackOperation('resize', ['width' => 200, 'height' => 200]));
        $stack->addStackOperation(new StackOperation('rotate', ['angle' => 45]));
        $stack->setStackOptions(['jpg.quality' => 80]);
        $stack->addStackOption('webp.quality', 80);
        $this->assertEquals('dynamic/resize-height-200-width-200--rotate-angle-45--options-jpg.quality-80-webp.quality-80', $stack->getDynamicUri());
    }
}

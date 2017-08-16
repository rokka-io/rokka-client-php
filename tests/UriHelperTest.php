<?php

namespace Rokka\Client\Tests;

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
            ['https://test.rokka.io/stackname/resize-width-100/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/stackname/resize-width-100--options-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6.jpg'],
            ['https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-3/b537639e539efcc3df4459ef87c5963aa5079ca6/seo.jpg', 'options-dpr-2', 'https://test.rokka.io/dynamic/resize-width-100--options-autoformat-true-dpr-2/b537639e539efcc3df4459ef87c5963aa5079ca6/seo.jpg'],
        ];
    }

    /**
     * @dataProvider provideAddOptionsToUri
     *
     * @param $inputUrl
     * @param $options
     * @param $expected
     *
     * @internal param $name
     */
    public function testAddOptionsToUri($inputUrl, $options, $expected)
    {
        $this->assertEquals($expected, UriHelper::addOptionsToUriString($inputUrl, $options));
    }
}

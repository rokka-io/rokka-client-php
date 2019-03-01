<?php

namespace Rokka\Client\Tests;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\Factory;
use Rokka\Client\Image;
use Rokka\Client\LocalImage\AbstractLocalImage;
use Rokka\Client\LocalImage\FileInfo;
use Rokka\Client\LocalImage\RokkaHash;
use Rokka\Client\LocalImage\StringContent;
use Rokka\Client\TemplateHelper;
use Rokka\Client\TemplateHelper\AbstractCallbacks;

class TemplateHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TemplateHelper
     */
    private $rokka;

    private $testImages = ['small' => ['path' => __DIR__.'/Fixtures/Images/small-bratpfanne.jpg', 'hash' => '71775293697709c1a1ce66f05d7c011a6982a6a9']];

    public function setUp()
    {
        $this->rokka = new TemplateHelper('testorg', 'key', new TestCallbacks());
        parent::setUp();
    }

    public function testGetHashMaybeUpload()
    {
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $this->assertEquals($this->testImages['small']['hash'], $this->rokka->getHashMaybeUpload($image));
    }

    public function provideStackUrl()
    {
        $testimage = $this->testImages['small']['path'];
        $urlPrefix = 'https://testorg.rokka.io/test/'.$this->testImages['small']['hash'];

        return [
            'SplFileInfo' => [new FileInfo(new \SplFileInfo($testimage)), null, $urlPrefix.'/small-bratpfanne.jpg'],
            'Native SplFileInfo' => [new \SplFileInfo($testimage), null, $urlPrefix.'/small-bratpfanne.jpg'],
            'String path' => [$testimage, null, $urlPrefix.'/small-bratpfanne.jpg'],
            'String content' => [new StringContent(file_get_contents($testimage)),  null, $urlPrefix.'.jpg'],
            'RokkaHash' => [new RokkaHash($this->testImages['small']['hash']),  null, $urlPrefix.'.jpg'],

            'SplFileInfo seo' => [new FileInfo(new \SplFileInfo($testimage)), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'Native SplFileInfo seo' => [new \SplFileInfo($testimage), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'String path seo' => [$testimage, 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'String content seo' => [new StringContent(file_get_contents($testimage)), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'RokkaHash seo' => [new RokkaHash($this->testImages['small']['hash']),  'seo-string', $urlPrefix.'/seo-string.jpg'],
        ];
    }

    /**
     * @dataProvider provideStackUrl
     *
     * @param $image
     * @param $seo
     * @param $url
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetStackUrl($image, $seo, $url)
    {
        $this->assertEquals($url, $this->rokka->getStackUrl($image, 'test', 'jpg', $seo));
    }

    public function testResizeUrl()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $urlPrefix = 'https://testorg.rokka.io/dynamic';

        $this->assertEquals(
            $urlPrefix."/resize-height-300-width-200--o-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeUrl($image, 200, 300)
        );
        $this->assertEquals(
            $urlPrefix."/resize-height-300-width-200--o-autoformat-true-jpg.transparency.autoformat-true/$hash/seo-straeng.png",
            $this->rokka->getResizeUrl($image, 200, 300, 'png', 'seo-sträng')
        );
        $this->assertEquals(
            $urlPrefix."/resize-height-300-width-200--o-autoformat-true-jpg.transparency.autoformat-true/$hash/seo-strang.png",
            $this->rokka->getResizeUrl($image, 200, 300, 'png', 'seo-sträng', 'latin')
        );
        $this->assertEquals(
            $urlPrefix."/resize-width-200--o-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeUrl($image, 200)
        );
    }

    public function testResizeCropURL()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $urlPrefix = 'https://testorg.rokka.io/dynamic';

        $this->assertEquals(
            $urlPrefix."/resize-height-300-mode-fill-width-200--crop-height-300-width-200--o-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeCropUrl($image, 200, 300)
        );
    }

    public function testGetSourceAttributes()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $this->rokka->getStackUrl($image, 'test');

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x"',
            $this->rokka->getSrcAttributes($url)
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x"',
            $this->rokka->getSrcAttributes($url, [2])
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x, https://testorg.rokka.io/test/o-dpr-3/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 3x"',
            $this->rokka->getSrcAttributes($url, ['2x', '3x'])
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2-jpg.quality-50/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x"',
            $this->rokka->getSrcAttributes($url, ['2x' => 'options-jpg.quality-50'])
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/resize-width-200/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 200w, https://testorg.rokka.io/test/resize-width-500/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 500w"',
            $this->rokka->getSrcAttributes($url, ['200w', '500w'])
        );
        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/resize-width-250--o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 500w"',
            $this->rokka->getSrcAttributes($url, ['500w' => '2x'])
        );
        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2--v-w-250/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 500w"',
            $this->rokka->getSrcAttributes($url, ['500w' => 'o-dpr-2--v-w-250'], false)
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/v-w-500/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 500w"',
            $this->rokka->getSrcAttributes($url, ['500w' => 'v-w-500'], false)
        );

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/o-dpr-2-j.q-50/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x"',
            $this->rokka->getSrcAttributes($url, ['2x' => 'o-j.q-50'], false)
        );

        // non rokka url
        $this->assertEquals(
            'src="https://some.example.com/files/image.jpg"',
            $this->rokka->getSrcAttributes('https://some.example.com/files/image.jpg', ['2x', '3x'])
        );
    }

    public function testGetBackgroundImageStyle()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $this->rokka->getStackUrl($image, 'test');

        $this->assertEquals(
            "background-image:url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg'); background-image: -webkit-image-set(url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 1x, url('https://testorg.rokka.io/test/o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 2x);",
            $this->rokka->getBackgroundImageStyle($url)
        );
        $this->assertEquals(
            "background-image:url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg'); background-image: -webkit-image-set(url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 1x, url('https://testorg.rokka.io/test/o-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 2x, url('https://testorg.rokka.io/test/o-dpr-3/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 3x);",
            $this->rokka->getBackgroundImageStyle($url, ['2x', '3x'])
        );
        $this->assertEquals(
            "background-image:url('https://some.example.com/files/image.jpg');",
            $this->rokka->getBackgroundImageStyle('https://some.example.com/files/image.jpg', ['2x', '3x'])
        );
    }

    public function testGetTemplateHelper()
    {
        $templateHelper = new TemplateHelper('testorg', 'key');
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($templateHelper->getRokkaClient()));
    }

    public function testGetTemplateHelperWithBase()
    {
        $templateHelper = new TemplateHelper(
            'testorg',
            'key',
            null,
            'https://liip.rokka.io/',
            'https://test.rokka.api/'
        );
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($templateHelper->getRokkaClient()));
    }

    public function testGetTemplateHelperWithNull()
    {
        $templateHelper = new TemplateHelper(
            'testorg',
            'key',
            null,
            'https://liip.rokka.io/',
            null
        );
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($templateHelper->getRokkaClient()));
    }

    public function testGetTemplateHelperWithOptions()
    {
        $templateHelper = new TemplateHelper(
            'testorg',
            'key',
            null,
            'https://liip.rokka.io/',
            [Factory::API_BASE_URL => 'https://test.rokka.api/']
        );
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($templateHelper->getRokkaClient()));
    }

    /**
     * @return array
     */
    public function provideSlugify()
    {
        return [
            ['hello-world.png', '', 'hello-world-png'],
            ['foo,bar@baz_hello world', '', 'foo-bar-baz-hello-world'],
            ['Etwas mit Ümlàuten', 'latin', 'etwas-mit-umlauten'],
            ['Etwas mit Ümlàüten', 'de', 'etwas-mit-uemlaueten'],
        ];
    }

    /**
     * @dataProvider provideSlugify
     *
     * @param string $input
     * @param string $lang
     * @param string $expected
     */
    public function testSlugify($input, $lang, $expected)
    {
        $this->assertEquals($expected, TemplateHelper::slugify($input, $lang));
    }

    private function checkBaseUrl($imageClient)
    {
        $reflector = new \ReflectionClass($imageClient);
        $reflector_property = $reflector->getProperty('client');
        $reflector_property->setAccessible(true);
        /** @var Client $client */
        $client = $reflector_property->getValue($imageClient);

        return (string) $client->getConfig('base_uri');
    }
}

class TestCallbacks extends AbstractCallbacks
{
    public function getHash(AbstractLocalImage $file)
    {
        return sha1($file->getContent());
    }

    public function saveHash(AbstractLocalImage $file, SourceImage $sourceImage)
    {
        // do nothing
    }
}

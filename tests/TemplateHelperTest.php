<?php

namespace Rokka\Client\Tests;

use Rokka\Client\LocalImage\FileInfo;
use Rokka\Client\LocalImage\LocalImageAbstract;
use Rokka\Client\LocalImage\RokkaHash;
use Rokka\Client\LocalImage\StringContent;
use Rokka\Client\TemplateHelper;
use Rokka\Client\TemplateHelperCallbacksAbstract;

class TemplateHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateHelper
     */
    private $rokka;

    private $testImages = ['small' => ['path' => __DIR__ . '/Fixtures/Images/small-bratpfanne.jpg', 'hash' => '71775293697709c1a1ce66f05d7c011a6982a6a9']];

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
     * @param $stack
     * @param $url
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
            $urlPrefix."/resize-width-200-height-300--options-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeUrl($image, 200, 300)
        );
        $this->assertEquals(
            $urlPrefix."/resize-width-200-height-300--options-autoformat-true-jpg.transparency.autoformat-true/$hash/seo-straeng.png",
            $this->rokka->getResizeUrl($image, 200, 300, 'png', 'seo-sträng')
        );
        $this->assertEquals(
            $urlPrefix."/resize-width-200-height-300--options-autoformat-true-jpg.transparency.autoformat-true/$hash/seo-strang.png",
            $this->rokka->getResizeUrl($image, 200, 300, 'png', 'seo-sträng', 'latin')
        );
        $this->assertEquals(
            $urlPrefix."/resize-width-200--options-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeUrl($image, 200)
        );
    }

    public function testResizeCropURL()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $urlPrefix = 'https://testorg.rokka.io/dynamic';

        $this->assertEquals(
            $urlPrefix."/resize-width-200-height-300-mode-fill--crop-width-200-height-300--options-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getResizeCropUrl($image, 200, 300)
        );
    }

    public function testGetSourceAttributes()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $this->rokka->getStackUrl($image, 'test');

        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/options-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x"',
            $this->rokka->getSrcAttributes($url)
        );
        $this->assertEquals(
            'src="https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg" srcset="https://testorg.rokka.io/test/options-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 2x https://testorg.rokka.io/test/options-dpr-3/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg 3x"',
            $this->rokka->getSrcAttributes($url, [2, 3])
        );
        // non rokka url
        $this->assertEquals(
            'src="https://some.example.com/files/image.jpg"',
            $this->rokka->getSrcAttributes('https://some.example.com/files/image.jpg', [2, 3])
        );
    }

    public function testGetBackgroundImageStyle()
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $this->rokka->getStackUrl($image, 'test');

        $this->assertEquals(
            "background-image:url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg'); background-image: -webkit-image-set(url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 1x, url('https://testorg.rokka.io/test/options-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 2x);",
            $this->rokka->getBackgroundImageStyle($url)
        );
        $this->assertEquals(
            "background-image:url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg'); background-image: -webkit-image-set(url('https://testorg.rokka.io/test/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 1x, url('https://testorg.rokka.io/test/options-dpr-2/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 2x, url('https://testorg.rokka.io/test/options-dpr-3/71775293697709c1a1ce66f05d7c011a6982a6a9/small-bratpfanne.jpg') 3x);",
            $this->rokka->getBackgroundImageStyle($url, [2, 3])
        );
        $this->assertEquals(
            "background-image:url('https://some.example.com/files/image.jpg');",
            $this->rokka->getBackgroundImageStyle('https://some.example.com/files/image.jpg', [2, 3])
        );
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
     * @param $inputUrl
     * @param $options
     * @param $expected
     */
    public function testSlugify($input, $lang, $expected)
    {
        $this->assertEquals($expected, TemplateHelper::slugify($input, $lang));
    }
}

class TestCallbacks extends TemplateHelperCallbacksAbstract
{
    public function getHash(LocalImageAbstract $file)
    {
        return sha1($file->getContent());
    }

    public function saveHash(LocalImageAbstract $file, $hash)
    {
        // do nothing
    }
}

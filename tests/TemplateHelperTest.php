<?php

namespace Rokka\Client\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
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

    protected function setUp(): void
    {
        $this->rokka = new TemplateHelper('testorg', 'key', new TestCallbacks());
        parent::setUp();
    }

    public function testGetHashMaybeUpload()
    {
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $this->assertEquals($this->testImages['small']['hash'], $this->rokka->getHashMaybeUpload($image));
    }

    public static function provideStackUrl()
    {
        $testimage = __DIR__.'/Fixtures/Images/small-bratpfanne.jpg';
        $hash = '71775293697709c1a1ce66f05d7c011a6982a6a9';
        $urlPrefix = 'https://testorg.rokka.io/test/'.$hash;

        return [
            'SplFileInfo' => [new FileInfo(new \SplFileInfo($testimage)), null, $urlPrefix.'/small-bratpfanne.jpg'],
            'Native SplFileInfo' => [new \SplFileInfo($testimage), null, $urlPrefix.'/small-bratpfanne.jpg'],
            'String path' => [$testimage, null, $urlPrefix.'/small-bratpfanne.jpg'],
            'String content' => [new StringContent(file_get_contents($testimage)),  null, $urlPrefix.'.jpg'],
            'RokkaHash' => [new RokkaHash($hash),  null, $urlPrefix.'.jpg'],

            'SplFileInfo seo' => [new FileInfo(new \SplFileInfo($testimage)), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'Native SplFileInfo seo' => [new \SplFileInfo($testimage), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'String path seo' => [$testimage, 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'String content seo' => [new StringContent(file_get_contents($testimage)), 'seo-string', $urlPrefix.'/seo-string.jpg'],
            'RokkaHash seo' => [new RokkaHash($hash),  'seo-string', $urlPrefix.'/seo-string.jpg'],
        ];
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[DataProvider('provideStackUrl')]
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
    public static function provideSlugify()
    {
        return [
            ['hello-world.png', '', 'hello-world-png'],
            ['foo,bar@baz_hello world', '', 'foo-bar-at-baz-hello-world'],
            ['Etwas mit Ümlàuten', 'latin', 'etwas-mit-umlauten'],
            ['Etwas mit Ümlàüten', 'de', 'etwas-mit-uemlaueten'],
        ];
    }

    /**
     * @param string $input
     * @param string $lang
     * @param string $expected
     */
    #[DataProvider('provideSlugify')]
    public function testSlugify($input, $lang, $expected)
    {
        $this->assertEquals($expected, TemplateHelper::slugify($input, $lang));
    }

    public function testGetOriginalSizeUrl(): void
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $urlPrefix = 'https://testorg.rokka.io/dynamic';

        $this->assertEquals(
            $urlPrefix."/noop--o-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.jpg",
            $this->rokka->getOriginalSizeUrl($image)
        );

        $this->assertEquals(
            $urlPrefix."/noop--o-autoformat-true-jpg.transparency.autoformat-true/$hash/small-bratpfanne.png",
            $this->rokka->getOriginalSizeUrl($image, 'png')
        );

        $this->assertEquals(
            $urlPrefix."/noop--o-autoformat-true-jpg.transparency.autoformat-true/$hash/my-seo-name.jpg",
            $this->rokka->getOriginalSizeUrl($image, 'jpg', 'my-seo-name')
        );
    }

    public function testResizeCropUrlWithSeo(): void
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $urlPrefix = 'https://testorg.rokka.io/dynamic';

        $this->assertEquals(
            $urlPrefix."/resize-height-400-mode-fill-width-300--crop-height-400-width-300--o-autoformat-true-jpg.transparency.autoformat-true/$hash/custom-seo.png",
            $this->rokka->getResizeCropUrl($image, 300, 400, 'png', 'custom-seo')
        );
    }

    public function testGetImageObjectWithAbstractLocalImage(): void
    {
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $result = $this->rokka->getImageObject($image);
        $this->assertSame($image, $result);
    }

    public function testGetImageObjectWithSplFileInfo(): void
    {
        $spl = new \SplFileInfo($this->testImages['small']['path']);
        $result = $this->rokka->getImageObject($spl);
        $this->assertInstanceOf(FileInfo::class, $result);
    }

    public function testGetImageObjectWithStringPath(): void
    {
        $result = $this->rokka->getImageObject($this->testImages['small']['path']);
        $this->assertInstanceOf(FileInfo::class, $result);
    }

    public function testGetImageObjectWithHashString(): void
    {
        $result = $this->rokka->getImageObject('abcdef1234567890abcd');
        $this->assertInstanceOf(RokkaHash::class, $result);
    }

    public function testGetImageObjectWithIdentifierAndContext(): void
    {
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $result = $this->rokka->getImageObject($image, 'my-id', ['key' => 'value']);
        $this->assertSame($image, $result);
        $this->assertSame('my-id', $result->getIdentifier());
        $this->assertSame(['key' => 'value'], $result->getContext());
    }

    public function testGetImageObjectThrowsOnInvalidType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not create a source image from input of type');
        $this->rokka->getImageObject(12345);
    }

    public function testGetStackUrlWithNullImage(): void
    {
        $this->assertSame('', $this->rokka->getStackUrl(null, 'test'));
    }

    public function testGenerateRokkaUrlWithNullFormat(): void
    {
        $hash = $this->testImages['small']['hash'];
        $url = $this->rokka->generateRokkaUrl($hash, 'test', null);
        $this->assertStringEndsWith('.jpg', $url);
    }

    public function testGetSrcSetUrl(): void
    {
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $this->rokka->getStackUrl($image, 'test');

        $srcSet = TemplateHelper::getSrcSetUrl($url, ['1x', '2x']);
        $this->assertStringContainsString('1x', $srcSet);
        $this->assertStringContainsString('2x', $srcSet);
        $this->assertStringContainsString(', ', $srcSet);
    }

    public function testGetImagename(): void
    {
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $this->assertEquals('small-bratpfanne', $this->rokka->getImagename($image));
    }

    public function testGetImagenameNull(): void
    {
        $this->assertEquals('', $this->rokka->getImagename(null));
    }

    public function testPublicDomainWithoutScheme(): void
    {
        $helper = new TemplateHelper('testorg', 'key', new TestCallbacks(), 'custom.rokka.io');
        $hash = $this->testImages['small']['hash'];
        $image = new FileInfo(new \SplFileInfo($this->testImages['small']['path']));
        $url = $helper->getStackUrl($image, 'test');
        $this->assertStringStartsWith('https://custom.rokka.io/', $url);
    }

    public static function provideRemoteOrStreamPaths(): \Generator
    {
        $fixture = __DIR__.'/Fixtures/Images/small-bratpfanne.jpg';
        $paths = [
            'http' => 'http://127.0.0.1:1/secret.png',
            'uppercase HTTP' => 'HTTP://127.0.0.1:1/secret.png',
            'https' => 'https://example.org/image.jpg',
            'php filter' => 'php://filter/read=convert.base64-encode/resource='.$fixture,
            'file' => 'file://'.$fixture,
            'phar' => 'phar://'.$fixture.'/x.jpg',
            'data' => 'data:image/png;base64,iVBORw0KGgo=',
            'uppercase DATA' => 'DATA:image/png;base64,iVBORw0KGgo=',
        ];
        $methods = [
            'getImageObject' => static fn (TemplateHelper $h, string $p) => $h->getImageObject($p),
            'getStackUrl' => static fn (TemplateHelper $h, string $p) => $h->getStackUrl($p, 'stack'),
            'getResizeUrl' => static fn (TemplateHelper $h, string $p) => $h->getResizeUrl($p, 100),
            'getResizeCropUrl' => static fn (TemplateHelper $h, string $p) => $h->getResizeCropUrl($p, 100, 100),
            'getOriginalSizeUrl' => static fn (TemplateHelper $h, string $p) => $h->getOriginalSizeUrl($p),
        ];
        foreach ($methods as $methodName => $method) {
            foreach ($paths as $pathName => $path) {
                yield $methodName.' '.$pathName => [$method, $path];
            }
        }
    }

    #[DataProvider('provideRemoteOrStreamPaths')]
    public function testRejectsUrlsAndStreamWrappers(\Closure $method, string $path): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only local file paths are supported');
        $method($this->rokka, $path);
    }

    public function testAllowsWindowsStylePath(): void
    {
        $result = $this->rokka->getImageObject('C:\\images\\image.jpg');
        $this->assertInstanceOf(FileInfo::class, $result);
    }

    public function testAllowedBaseDirAcceptsPathInside(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/Images']);
        $path = $this->testImages['small']['path'];

        $this->assertInstanceOf(FileInfo::class, $this->rokka->getImageObject($path));
        $this->assertInstanceOf(FileInfo::class, $this->rokka->getImageObject(new \SplFileInfo($path)));
        $this->assertStringEndsWith('/small-bratpfanne.jpg', $this->rokka->getStackUrl($path, 'stack'));
    }

    public function testAllowedBaseDirAcceptsRelativeDotDotStayingInside(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures']);
        $path = __DIR__.'/Fixtures/Images/../Images/small-bratpfanne.jpg';

        $this->assertInstanceOf(FileInfo::class, $this->rokka->getImageObject($path));
    }

    public function testAllowedBaseDirRejectsPathOutside(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/Images']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not inside an allowed base dir');
        $this->rokka->getStackUrl(__FILE__, 'stack');
    }

    public function testAllowedBaseDirRejectsDotDotEscape(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/Images']);

        $this->expectException(\InvalidArgumentException::class);
        $this->rokka->getImageObject(__DIR__.'/Fixtures/Images/../../TemplateHelperTest.php');
    }

    public function testAllowedBaseDirRejectsSplFileInfoOutside(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/Images']);

        $this->expectException(\InvalidArgumentException::class);
        $this->rokka->getImageObject(new \SplFileInfo(__FILE__));
    }

    public function testAllowedBaseDirRejectsMissingFile(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/Images']);

        $this->expectException(\InvalidArgumentException::class);
        $this->rokka->getImageObject(__DIR__.'/Fixtures/Images/does-not-exist.jpg');
    }

    public function testAllowedBaseDirRejectsSiblingWithSamePrefix(): void
    {
        $tmp = $this->createTempDir();
        mkdir($tmp.'/base');
        mkdir($tmp.'/base-evil');
        copy($this->testImages['small']['path'], $tmp.'/base-evil/image.jpg');
        try {
            $this->rokka->setAllowedBaseDirs([$tmp.'/base']);
            $this->expectException(\InvalidArgumentException::class);
            $this->rokka->getImageObject($tmp.'/base-evil/image.jpg');
        } finally {
            $this->removeTempDir($tmp);
        }
    }

    public function testAllowedBaseDirRejectsSymlinkPointingOutside(): void
    {
        $tmp = $this->createTempDir();
        mkdir($tmp.'/base');
        if (!@symlink(__FILE__, $tmp.'/base/link.jpg')) {
            $this->removeTempDir($tmp);
            $this->markTestSkipped('symlinks not supported');
        }
        try {
            $this->rokka->setAllowedBaseDirs([$tmp.'/base']);
            $this->expectException(\InvalidArgumentException::class);
            $this->rokka->getImageObject($tmp.'/base/link.jpg');
        } finally {
            $this->removeTempDir($tmp);
        }
    }

    public function testSetAllowedBaseDirsResolvesAndClears(): void
    {
        $this->rokka->setAllowedBaseDirs([__DIR__.'/Fixtures/../Fixtures/Images/']);
        $this->assertSame([realpath(__DIR__.'/Fixtures/Images').\DIRECTORY_SEPARATOR], $this->rokka->getAllowedBaseDirs());

        $this->rokka->setAllowedBaseDirs([]);
        $this->assertSame([], $this->rokka->getAllowedBaseDirs());
        $this->assertInstanceOf(FileInfo::class, $this->rokka->getImageObject(__FILE__));
    }

    public function testSetAllowedBaseDirsRejectsMissingDir(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allowed base dir does not exist');
        $this->rokka->setAllowedBaseDirs([__DIR__.'/does-not-exist']);
    }

    private function createTempDir(): string
    {
        $tmp = sys_get_temp_dir().'/rokka-test-'.bin2hex(random_bytes(6));
        mkdir($tmp);

        return (string) realpath($tmp);
    }

    private function removeTempDir(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            /* @var \SplFileInfo $item */
            $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    private function checkBaseUrl($imageClient)
    {
        $reflector = new \ReflectionClass($imageClient);
        $reflector_property = $reflector->getProperty('client');
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

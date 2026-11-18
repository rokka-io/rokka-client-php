<?php

namespace Rokka\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Rokka\Client\Core\SourceImageAlias;
use Rokka\Client\Image;

/**
 * Tests Image API methods using a Guzzle MockHandler so no real HTTP traffic occurs.
 *
 * Each test instantiates Image directly with a mock client, queues canned responses,
 * and asserts the recorded outgoing requests match the documented Rokka API endpoints.
 */
class ImageApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<int, array{request: RequestInterface, options: array}>
     */
    private array $history = [];

    private function makeClient(array $responses): Image
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));
        $client = new Client(['handler' => $stack, 'base_uri' => 'https://api.rokka.io/']);

        return new Image($client, 'testorg', 'test-api-key');
    }

    // ---- Section 1: Source image gaps ----

    public function testSetSourceImageName(): void
    {
        $image = $this->makeClient([new Response(204)]);

        $result = $image->setSourceImageName('new-name.jpg', 'abc123');

        $this->assertTrue($result);
        $req = $this->history[0]['request'];
        $this->assertSame('PUT', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/name', $req->getUri()->getPath());
        $this->assertSame('"new-name.jpg"', (string) $req->getBody());
    }

    public function testSetSourceImageNameReturnsFalseOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertFalse($image->setSourceImageName('foo.jpg', 'abc123'));
    }

    public function testDeleteSourceImageCache(): void
    {
        $body = '{"items":["/stack1/abc123.jpg","/stack2/abc123.png"]}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $result = $image->deleteSourceImageCache('abc123');

        $this->assertSame(['/stack1/abc123.jpg', '/stack2/abc123.png'], $result);
        $req = $this->history[0]['request'];
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/cache', $req->getUri()->getPath());
    }

    public function testDeleteSourceImageCacheReturnsEmptyOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertSame([], $image->deleteSourceImageCache('abc123'));
    }

    public function testDownloadSourceImagesAsZip(): void
    {
        $zipBytes = "PK\x03\x04fake-zip-content";
        $image = $this->makeClient([new Response(200, ['Content-Type' => 'application/zip'], $zipBytes)]);

        $result = $image->downloadSourceImagesAsZip(50, null, ['created' => 'desc'], false);

        $this->assertSame($zipBytes, $result);
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/download', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('50', $query['limit']);
        $this->assertSame('created desc', $query['sort']);
        $this->assertArrayNotHasKey('deleted', $query);
    }

    public function testDownloadSourceImagesAsZipWithDeletedFlag(): void
    {
        $image = $this->makeClient([new Response(200, [], 'PK')]);

        $image->downloadSourceImagesAsZip(null, null, [], true);

        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame('true', $query['deleted']);
    }

    public function testCopySourceImageUsesPostEndpoint(): void
    {
        $image = $this->makeClient([new Response(201)]);

        $result = $image->copySourceImage('abc123', 'destorg');

        $this->assertTrue($result);
        $req = $this->history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/copy', $req->getUri()->getPath());
        $this->assertSame('destorg', $req->getHeaderLine('Destination'));
        $this->assertSame('', $req->getHeaderLine('Overwrite'));
    }

    public function testCopySourceImageWithoutOverwriteSetsHeader(): void
    {
        $image = $this->makeClient([new Response(201)]);

        $image->copySourceImage('abc123', 'destorg', false);

        $this->assertSame('F', $this->history[0]['request']->getHeaderLine('Overwrite'));
    }

    public function testGetSourceImagesWithBinaryHashUsesDedicatedPath(): void
    {
        $body = '{"items":[],"total":0,"cursor":""}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $image->getSourceImagesWithBinaryHash('abc123binary');

        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/binaryhash/abc123binary', $req->getUri()->getPath());
        $this->assertSame('', $req->getUri()->getQuery());
    }

    // ---- Section 2: Image aliases ----

    public function testGetSourceImageAlias(): void
    {
        $body = '{"organization":"testorg","alias":"my-alias","hash":"abc123"}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $alias = $image->getSourceImageAlias('my-alias');

        $this->assertInstanceOf(SourceImageAlias::class, $alias);
        $this->assertSame('testorg', $alias->getOrganization());
        $this->assertSame('my-alias', $alias->getAlias());
        $this->assertSame('abc123', $alias->getHash());
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/alias/my-alias', $req->getUri()->getPath());
    }

    public function testSetSourceImageAlias(): void
    {
        $body = '{"organization":"testorg","alias":"my-alias","hash":"abc123"}';
        $image = $this->makeClient([new Response(201, [], $body)]);

        $alias = $image->setSourceImageAlias('my-alias', 'abc123');

        $this->assertSame('abc123', $alias->getHash());
        $req = $this->history[0]['request'];
        $this->assertSame('PUT', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/alias/my-alias', $req->getUri()->getPath());
        $this->assertSame('{"hash":"abc123"}', (string) $req->getBody());
        $this->assertSame('', $req->getUri()->getQuery());
    }

    public function testSetSourceImageAliasWithOverwrite(): void
    {
        $body = '{"organization":"testorg","alias":"my-alias","hash":"abc123"}';
        $image = $this->makeClient([new Response(201, [], $body)]);

        $image->setSourceImageAlias('my-alias', 'abc123', '', ['overwrite' => true]);

        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame('true', $query['overwrite']);
    }

    public function testDeleteSourceImageAlias(): void
    {
        $image = $this->makeClient([new Response(204)]);

        $this->assertTrue($image->deleteSourceImageAlias('my-alias'));
        $req = $this->history[0]['request'];
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/alias/my-alias', $req->getUri()->getPath());
    }

    public function testDeleteSourceImageAliasReturnsFalseOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertFalse($image->deleteSourceImageAlias('my-alias'));
    }

    public function testDeleteSourceImageAliasCache(): void
    {
        $body = '{"items":["/stack/my-alias.jpg"]}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $result = $image->deleteSourceImageAliasCache('my-alias');

        $this->assertSame(['/stack/my-alias.jpg'], $result);
        $req = $this->history[0]['request'];
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/alias/my-alias/cache', $req->getUri()->getPath());
    }

    public function testDeleteSourceImageAliasCacheReturnsEmptyOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertSame([], $image->deleteSourceImageAliasCache('my-alias'));
    }

    // ---- Section 4: Stack cache, stack options, sign URL ----

    public function testDeleteStackCache(): void
    {
        $image = $this->makeClient([new Response(200, [], '{"status":"ok"}')]);

        $this->assertTrue($image->deleteStackCache('mystack'));
        $req = $this->history[0]['request'];
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/stacks/testorg/mystack/cache', $req->getUri()->getPath());
    }

    public function testDeleteStackCacheReturnsFalseOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertFalse($image->deleteStackCache('missing'));
    }

    public function testDeleteStackCacheWildcard(): void
    {
        $image = $this->makeClient([new Response(200, [], '{"status":"ok"}')]);

        $image->deleteStackCache('*');

        $this->assertSame('/stacks/testorg/*/cache', $this->history[0]['request']->getUri()->getPath());
    }

    public function testListStackOptions(): void
    {
        $body = '{"properties":{"jpg.quality":{"type":"integer","minimum":1,"maximum":100,"default":75}}}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $result = $image->listStackOptions();

        $this->assertArrayHasKey('properties', $result);
        $this->assertSame(75, $result['properties']['jpg.quality']['default']);
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/stackoptions', $req->getUri()->getPath());
    }

    public function testSignUrlOnServer(): void
    {
        $body = '{"signed_url":"https://x.rokka.io/foo.jpg?sig=abc","original_url":"https://x.rokka.io/foo.jpg"}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $result = $image->signUrlOnServer('https://x.rokka.io/foo.jpg');

        $this->assertSame('https://x.rokka.io/foo.jpg?sig=abc', $result['signed_url']);
        $req = $this->history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/utils/testorg/sign_url', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('https://x.rokka.io/foo.jpg', $query['url']);
        $this->assertArrayNotHasKey('key', $query);
        $this->assertArrayNotHasKey('until', $query);
    }

    public function testSignUrlOnServerWithAllOptions(): void
    {
        $body = '{"signed_url":"https://x.rokka.io/foo.jpg?sig=abc&exp=1","original_url":"https://x.rokka.io/foo.jpg","until":"2026-12-31T23:59:59+00:00"}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $until = new \DateTime('2026-12-31T23:59:59+00:00');
        $image->signUrlOnServer('https://x.rokka.io/foo.jpg', 'mykey', $until, 60);

        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame('mykey', $query['key']);
        $this->assertSame('60', $query['roundDateUpTo']);
        $this->assertStringStartsWith('2026-12-31', $query['until']);
    }

    // ---- Section 3: User metadata single-field ----

    public function testGetUserMetadata(): void
    {
        $body = '{"foo":"bar","int:count":5}';
        $image = $this->makeClient([new Response(200, [], $body)]);

        $result = $image->getUserMetadata('abc123');

        $this->assertSame(['foo' => 'bar', 'int:count' => 5], $result);
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/meta/user', $req->getUri()->getPath());
    }

    public function testGetUserMetadataField(): void
    {
        $image = $this->makeClient([new Response(200, [], '"bar"')]);

        $result = $image->getUserMetadataField('foo', 'abc123');

        $this->assertSame('bar', $result);
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/meta/user/foo', $req->getUri()->getPath());
    }

    public function testGetUserMetadataFieldReturnsNullOnNotFound(): void
    {
        $image = $this->makeClient([new Response(404, [], '{"error":"not found"}')]);

        $this->assertNull($image->getUserMetadataField('missing', 'abc123'));
    }

    // ---- keepHash option on dynamic metadata ----

    public function testSetDynamicMetadataWithKeepHash(): void
    {
        $image = $this->makeClient([new Response(204, ['Location' => '/sourceimages/testorg/abc123'])]);

        $image->setDynamicMetadata(['subject_area' => ['x' => 1, 'y' => 2, 'width' => 3, 'height' => 4]], 'abc123', '', ['keepHash' => true]);

        $req = $this->history[0]['request'];
        $this->assertSame('PUT', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/meta/dynamic/subject_area', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('true', $query['keepHash']);
        $this->assertArrayNotHasKey('deletePrevious', $query);
    }

    public function testSetDynamicMetadataRejectsKeepHashWithDeletePrevious(): void
    {
        $image = $this->makeClient([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('keepHash and deletePrevious are mutually exclusive');

        $image->setDynamicMetadata(['subject_area' => ['x' => 1]], 'abc123', '', ['keepHash' => true, 'deletePrevious' => true]);
    }

    public function testDeleteDynamicMetadataWithKeepHash(): void
    {
        $image = $this->makeClient([new Response(204, ['Location' => '/sourceimages/testorg/abc123'])]);

        $image->deleteDynamicMetadata('subject_area', 'abc123', '', ['keepHash' => true]);

        $req = $this->history[0]['request'];
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/sourceimages/testorg/abc123/meta/dynamic/subject_area', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('true', $query['keepHash']);
    }

    public function testDeleteDynamicMetadataRejectsKeepHashWithDeletePrevious(): void
    {
        $image = $this->makeClient([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('keepHash and deletePrevious are mutually exclusive');

        $image->deleteDynamicMetadata('subject_area', 'abc123', '', ['keepHash' => true, 'deletePrevious' => true]);
    }

    // ---- Section: multipart upload headers ----

    /**
     * Splits the recorded multipart request body into parts and returns the header lines of each part.
     *
     * @return list<list<string>>
     */
    private function getMultipartHeaders(RequestInterface $request): array
    {
        $this->assertSame(1, preg_match('/boundary=([^;]+)/', $request->getHeaderLine('Content-Type'), $m));
        $parts = explode('--'.$m[1], (string) $request->getBody());
        // drop the preamble and the closing "--\r\n"
        $parts = \array_slice($parts, 1, -1);

        return array_map(static function (string $part): array {
            [$headers] = explode("\r\n\r\n", ltrim($part, "\r\n"), 2);

            return explode("\r\n", $headers);
        }, $parts);
    }

    /**
     * @param list<string> $headers
     */
    private function assertSingleContentDisposition(string $expected, array $headers): void
    {
        $dispositions = array_values(array_filter($headers, static fn (string $h): bool => 0 === stripos($h, 'Content-Disposition:')));
        $this->assertSame(['Content-Disposition: '.$expected], $dispositions);
    }

    public static function provideUploadFilenames(): array
    {
        return [
            'plain' => ['photo.jpg', 'photo.jpg'],
            'spaces, umlaut and percent' => ['Mein Bild 100% süss.jpg', 'Mein Bild 100% süss.jpg'],
            'quote adds a name parameter' => ['x.gif"; name="options[0]', 'x.gif%22; name=%22options[0]'],
            'crlf adds a header' => ["x.gif\"\r\nContent-Disposition: form-data; name=\"meta_user[0]", 'x.gif%22%0D%0AContent-Disposition: form-data; name=%22meta_user[0]'],
            'lf only' => ["x\nX-Foo: bar.gif", 'x%0AX-Foo: bar.gif'],
            'nul' => ["x\0.gif", 'x%00.gif'],
            'trailing backslash' => ['x.gif\\', 'x.gif%5C'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideUploadFilenames')]
    public function testUploadSourceImageEscapesFilename(string $fileName, string $expected): void
    {
        $image = $this->makeClient([new Response(200, [], '{"items":[]}')]);

        $image->uploadSourceImage('GIF89a', $fileName, '', ['meta_user' => ['foo' => 'bar']]);

        $req = $this->history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/sourceimages/testorg', $req->getUri()->getPath());

        $parts = $this->getMultipartHeaders($req);
        $this->assertCount(2, $parts);
        $this->assertSingleContentDisposition('form-data; name="filedata"; filename="'.$expected.'"', $parts[0]);
        $this->assertSame(['Content-Length', 'Content-Type'], array_map(static fn (string $h): string => explode(':', $h)[0], \array_slice($parts[0], 1)));
        $this->assertSingleContentDisposition('form-data; name="meta_user[0]"', $parts[1]);
    }

    public function testUploadSourceImageSendsDynamicMetadata(): void
    {
        $image = $this->makeClient([new Response(200, [], '{"items":[]}')]);

        $image->uploadSourceImage('GIF89a', 'x.gif', '', ['meta_dynamic' => [
            // the key is ignored for DynamicMetadataInterface objects, getName() is used
            'not valid!' => new \Rokka\Client\Core\DynamicMetadata\SubjectArea(1, 2),
            'crop_area' => ['x' => 1, 'y' => 2, 'width' => 3, 'height' => 4],
        ]]);

        $parts = $this->getMultipartHeaders($this->history[0]['request']);
        $this->assertCount(3, $parts);
        $this->assertSingleContentDisposition('form-data; name="meta_dynamic[0][subject_area]"', $parts[1]);
        $this->assertSingleContentDisposition('form-data; name="meta_dynamic[0][crop_area]"', $parts[2]);
    }

    public static function provideInvalidDynamicMetadataNames(): array
    {
        return [
            'bracket' => ['x][y'],
            'quote' => ['x]"; name="options[0]'],
            'crlf' => ["x\r\nX-Foo: bar"],
            'trailing newline' => ["crop_area\n"],
            'uppercase' => ['Crop_Area'],
            'empty' => [''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidDynamicMetadataNames')]
    public function testUploadSourceImageRejectsInvalidDynamicMetadataName(string $name): void
    {
        $image = $this->makeClient([new Response(200, [], '{"items":[]}')]);

        try {
            $image->uploadSourceImage('GIF89a', 'x.gif', '', ['meta_dynamic' => [$name => ['x' => 1]]]);
            $this->fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Invalid dynamic metadata name', $e->getMessage());
        }
        $this->assertSame([], $this->history);
    }

    public function testUploadSourceImageByUrlRejectsInvalidDynamicMetadataName(): void
    {
        $image = $this->makeClient([new Response(200, [], '{"items":[]}')]);

        $this->expectException(\InvalidArgumentException::class);

        $image->uploadSourceImageByUrl('https://example.org/x.gif', '', ['meta_dynamic' => ['x]' => ['x' => 1]]]);
    }
}

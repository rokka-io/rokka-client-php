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

}

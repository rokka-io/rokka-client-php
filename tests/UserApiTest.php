<?php

namespace Rokka\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Rokka\Client\Core\Organization;
use Rokka\Client\User;

/**
 * Tests User API methods using a Guzzle MockHandler so no real HTTP traffic occurs.
 */
class UserApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<int, array{request: RequestInterface, options: array}>
     */
    private array $history = [];

    private function makeClient(array $responses): User
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));
        $client = new Client(['handler' => $stack, 'base_uri' => 'https://api.rokka.io/']);

        return new User($client, 'testorg', 'test-api-key');
    }

    public function testGetBilling(): void
    {
        $body = '{"total":42.5,"images":1234,"traffic":56789}';
        $user = $this->makeClient([new Response(200, [], $body)]);

        $result = $user->getBilling('testorg');

        $this->assertSame(['total' => 42.5, 'images' => 1234, 'traffic' => 56789], $result);
        $req = $this->history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/billing/testorg', $req->getUri()->getPath());
        $this->assertSame('', $req->getUri()->getQuery());
    }

    public function testGetBillingWithDateRange(): void
    {
        $user = $this->makeClient([new Response(200, [], '{}')]);

        $from = new \DateTime('2026-01-01');
        $to = new \DateTime('2026-03-31');
        $user->getBilling('testorg', $from, $to);

        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame('2026-01-01', $query['from']);
        $this->assertSame('2026-03-31', $query['to']);
    }

    public function testSetOrganizationOptions(): void
    {
        $body = '{"id":"abc","name":"testorg","display_name":"Test Org","billing_email":"billing@example.com","options":{"foo":"bar","baz":42}}';
        $user = $this->makeClient([new Response(200, [], $body)]);

        $org = $user->setOrganizationOptions('testorg', ['foo' => 'bar', 'baz' => 42]);

        $this->assertInstanceOf(Organization::class, $org);
        $req = $this->history[0]['request'];
        $this->assertSame('PUT', $req->getMethod());
        $this->assertSame('/organizations/testorg/options', $req->getUri()->getPath());
        $this->assertSame('{"foo":"bar","baz":42}', (string) $req->getBody());
    }
}

<?php

namespace Rokka\Client\Tests;

use Rokka\Client\Factory;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    private function createClient()
    {
        return Factory::getImageClient('testorg', 'apiKey');
    }

    /**
     * Helper to create a JWT token with a given payload (no signature verification needed).
     */
    private function createToken(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = 'fakesignature';

        return $header.'.'.$body.'.'.$signature;
    }

    public function testGetTokenPayloadReturnsPayload(): void
    {
        $client = $this->createClient();
        $payload = ['sub' => 'user123', 'exp' => 9999999999, 'iss' => 'rokka'];
        $token = $this->createToken($payload);

        $result = $client->getTokenPayload($token);

        $this->assertIsArray($result);
        $this->assertSame('user123', $result['sub']);
        $this->assertSame(9999999999, $result['exp']);
        $this->assertSame('rokka', $result['iss']);
    }

    public function testGetTokenPayloadReturnsNullForInvalidToken(): void
    {
        $client = $this->createClient();

        $this->assertNull($client->getTokenPayload('not-a-jwt'));
        $this->assertNull($client->getTokenPayload('only.two'));
        $this->assertNull($client->getTokenPayload(''));
    }

    public function testGetTokenPayloadReturnsNullForInvalidBase64(): void
    {
        $client = $this->createClient();

        // Valid structure but invalid base64 in payload
        $this->assertNull($client->getTokenPayload('header.!!!invalid!!!.signature'));
    }

    public function testGetTokenPayloadReturnsNullForInvalidJson(): void
    {
        $client = $this->createClient();

        // Valid base64 but not valid JSON
        $notJson = rtrim(strtr(base64_encode('not json'), '+/', '-_'), '=');
        $this->assertNull($client->getTokenPayload('header.'.$notJson.'.signature'));
    }

    public function testGetTokenPayloadUsesStoredToken(): void
    {
        $client = $this->createClient();
        $payload = ['sub' => 'stored-user'];
        $token = $this->createToken($payload);

        $client->setToken($token);
        $result = $client->getTokenPayload();

        $this->assertIsArray($result);
        $this->assertSame('stored-user', $result['sub']);
    }

    public function testGetTokenPayloadReturnsNullWhenNoToken(): void
    {
        $client = $this->createClient();

        $this->assertNull($client->getTokenPayload());
    }

    public function testGetTokenIsValidForReturnsSeconds(): void
    {
        $client = $this->createClient();
        $exp = time() + 3600;
        $token = $this->createToken(['exp' => $exp]);

        $validFor = $client->getTokenIsValidFor($token);

        // Should be close to 3600, allow some tolerance for test execution time
        $this->assertGreaterThan(3590, $validFor);
        $this->assertLessThanOrEqual(3600, $validFor);
    }

    public function testGetTokenIsValidForReturnsNegativeForExpiredToken(): void
    {
        $client = $this->createClient();
        $token = $this->createToken(['exp' => time() - 100]);

        $this->assertLessThan(0, $client->getTokenIsValidFor($token));
    }

    public function testGetTokenIsValidForReturnsMinusOneWithoutExp(): void
    {
        $client = $this->createClient();
        $token = $this->createToken(['sub' => 'user']);

        $this->assertSame(-1, $client->getTokenIsValidFor($token));
    }

    public function testGetTokenIsValidForReturnsMinusOneWithoutToken(): void
    {
        $client = $this->createClient();

        $this->assertSame(-1, $client->getTokenIsValidFor());
    }
}

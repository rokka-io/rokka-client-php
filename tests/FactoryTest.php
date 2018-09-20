<?php

namespace Rokka\Client\Tests;

use GuzzleHttp\Client;
use Rokka\Client\Factory;
use Rokka\Client\Image;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetImageClient()
    {
        $imageClient = Factory::getImageClient('testorganization', 'testKey');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientSecret()
    {
        // old signature with apiSecret
        $imageClient = Factory::getImageClient('testorganization', 'testKey', 'testSignature');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientBase()
    {
        $imageClient = Factory::getImageClient('testorganization', 'testKey', 'https://test.rokka.api/');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientSecretBase()
    {
        // old signature with apiSecret
        $imageClient = Factory::getImageClient('testorganization', 'testKey', 'testSignature', 'https://test.rokka.api/');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientBaseSecretEmpty()
    {
        // old signature with apiSecret
        $imageClient = Factory::getImageClient('testorganization', 'testKey', '', 'https://test.rokka.api/');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientBaseOptions()
    {
        // old signature with apiSecret
        $imageClient = Factory::getImageClient('testorganization', 'testKey', ['api_base_url' => 'https://test.rokka.api/']);

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($imageClient));
    }

    public function testGetImageClientSecretEmpty()
    {
        // old signature with apiSecret
        $imageClient = Factory::getImageClient('testorganization', 'testKey', '');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($imageClient));
    }

    public function testGetUserClient()
    {
        $userClient = Factory::getUserClient();

        $this->assertInstanceOf('\\Rokka\\Client\\User', $userClient);
        $this->assertEquals(Image::DEFAULT_API_BASE_URL, $this->checkBaseUrl($userClient));
    }

    public function testGetUserClientBase()
    {
        $userClient = Factory::getUserClient('https://test.rokka.api/');

        $this->assertInstanceOf('\\Rokka\\Client\\User', $userClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($userClient));
    }

    public function testGetUserClientBaseOptions()
    {
        $userClient = Factory::getUserClient([Factory::API_BASE_URL => 'https://test.rokka.api/']);

        $this->assertInstanceOf('\\Rokka\\Client\\User', $userClient);
        $this->assertEquals('https://test.rokka.api/', $this->checkBaseUrl($userClient));
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

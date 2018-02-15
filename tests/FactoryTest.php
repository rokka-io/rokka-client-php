<?php

namespace Rokka\Client\Tests;

use Rokka\Client\Factory;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetImageClient()
    {
        $imageClient = Factory::getImageClient('testorganization', 'testKey', 'testSignature');

        $this->assertInstanceOf('\\Rokka\\Client\\Image', $imageClient);
    }

    public function testGetUserClient()
    {
        $userClient = Factory::getUserClient();

        $this->assertInstanceOf('\\Rokka\\Client\\User', $userClient);
    }
}

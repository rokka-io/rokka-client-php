<?php

namespace Rokka\Client\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Rokka\Client\Core\StackUri;
use Rokka\Client\Core\UriComponents;

class UriComponentTest extends \PHPUnit\Framework\TestCase
{
    public static function provideCreateFromArray()
    {
        return [
            'simple stack' => [['stack' => 'foo', 'filename' => 'test', 'hash' => '34d345', 'format' => 'jpg'], 'foo'],
            'dynamic stack' => [['stack' => 'dynamic/options-dpr-2', 'filename' => 'test', 'hash' => '34d345', 'format' => 'jpg'], 'dynamic'],
            'StackUri object' => [['stack' => new StackUri('foo'), 'filename' => 'test', 'hash' => '34d345', 'format' => 'jpg'], 'foo'],
        ];
    }

    /**
     * @param array $config
     */
    #[DataProvider('provideCreateFromArray')]
    public function testCreateFromArray($config, $name)
    {
        $components = UriComponents::createFromArray($config);
        $stack = $components->getStack();
        $this->assertEquals($name, $stack->getName());
        $this->assertEquals($config['filename'], $components->getFilename());
        $this->assertEquals($config['hash'], $components->getHash());
        $this->assertEquals($config['format'], $components->getFormat());
    }
}

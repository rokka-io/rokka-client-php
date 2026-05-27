<?php

namespace Core;

use Rokka\Client\Core\SourceImageAlias;

class SourceImageAliasTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorAndGetters(): void
    {
        $alias = new SourceImageAlias('testorg', 'my-alias', 'abc123');

        $this->assertSame('testorg', $alias->getOrganization());
        $this->assertSame('my-alias', $alias->getAlias());
        $this->assertSame('abc123', $alias->getHash());
    }

    public function testCreateFromDecodedJsonResponse(): void
    {
        $alias = SourceImageAlias::createFromDecodedJsonResponse([
            'organization' => 'testorg',
            'alias' => 'my-alias',
            'hash' => 'abc123def456',
        ]);

        $this->assertSame('testorg', $alias->getOrganization());
        $this->assertSame('my-alias', $alias->getAlias());
        $this->assertSame('abc123def456', $alias->getHash());
    }

    public function testCreateFromDecodedJsonResponseDefaultsMissingFields(): void
    {
        $alias = SourceImageAlias::createFromDecodedJsonResponse([]);

        $this->assertSame('', $alias->getOrganization());
        $this->assertSame('', $alias->getAlias());
        $this->assertSame('', $alias->getHash());
    }
}

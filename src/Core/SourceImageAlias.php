<?php

namespace Rokka\Client\Core;

/**
 * Represents an alias for a source image hash within an organization.
 */
class SourceImageAlias
{
    /**
     * @var string
     */
    private $organization;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param string $organization Organization name
     * @param string $alias        Alias name
     * @param string $hash         Source image hash the alias points to
     */
    public function __construct($organization, $alias, $hash)
    {
        $this->organization = $organization;
        $this->alias = $alias;
        $this->hash = $hash;
    }

    /**
     * Create from a decoded JSON response returned by the rokka.io API.
     *
     * @param array $data Decoded JSON data
     *
     * @return SourceImageAlias
     */
    public static function createFromDecodedJsonResponse(array $data)
    {
        return new self($data['organization'] ?? '', $data['alias'] ?? '', $data['hash'] ?? '');
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}

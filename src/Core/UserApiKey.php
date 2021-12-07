<?php

namespace Rokka\Client\Core;

class UserApiKey
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTime|null
     */
    private $created;

    /**
     * @var \DateTime|null
     */
    private $accessed;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * Constructor.
     *
     * @param string         $id       Api-Key Id
     * @param \DateTime|null $created  Creation Date of the Key
     * @param \DateTime|null $accessed Last Access (only recorded once per 24h)
     * @param string|null    $comment  Optional Comment
     * @param string|null    $apiKey   The Api Key on creation
     */
    public function __construct($id, $created, $accessed, $comment, $apiKey)
    {
        $this->id = $id;
        $this->accessed = $accessed;
        $this->created = $created;
        $this->comment = $comment;
        $this->apiKey = $apiKey;
    }

    /**
     * Create a user from the JSON data returned by the rokka.io API.
     *
     * @param array $apiKeys
     *
     * @return \Rokka\Client\Core\UserApiKey
     */
    public static function createFromArray($apiKeys)
    {
        return new self($apiKeys['id'], $apiKeys['created'] ?? null, $apiKeys['accessed'] ?? null, $apiKeys['comment'] ?? null, $apiKeys['api_key'] ?? null);
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getAccessed(): ?\DateTime
    {
        return $this->accessed;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
}

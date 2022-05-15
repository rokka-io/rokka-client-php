<?php

namespace Rokka\Client\Core;

class UserApiToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $payload;

    /**
     * Constructor.
     *
     * @param string $token   Api-Token
     * @param array  $payload the payload
     */
    public function __construct($token, $payload)
    {
        $this->token = $token;
        $this->payload = $payload;
    }

    /**
     * Create a user from the JSON data returned by the rokka.io API.
     *
     * @param array $resonse
     *
     * @return \Rokka\Client\Core\UserApiToken
     */
    public static function createFromArray($resonse)
    {
        return new self($resonse['token'], $resonse['payload']);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}

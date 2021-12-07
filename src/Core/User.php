<?php

namespace Rokka\Client\Core;

/**
 * Represents a user.
 */
class User
{
    /**
     * UUID v4.
     *
     * @var string
     */
    public $id;

    /**
     * Email of user.
     *
     * @var string|null
     */
    public $email;

    /**
     * Api Key.
     *
     * @var string|null
     */
    public $apiKey;

    /**
     * The Api Keys for this user.
     *
     * @var array
     */
    private $apiKeys;

    /**
     * Constructor.
     *
     * @param string                               $id      Id
     * @param string|null                          $email   Email
     * @param string|null                          $apiKey  API keys
     * @param array<\Rokka\Client\Core\UserApiKey> $apiKeys API keys
     */
    public function __construct($id, $email, $apiKey, $apiKeys = [])
    {
        $this->id = $id;
        $this->email = $email;

        $this->apiKey = $apiKey;
        $this->apiKeys = $apiKeys;
    }

    /**
     * Create a user from the JSON data returned by the rokka.io API.
     *
     * @param string $jsonString JSON as a string
     *
     * @return User
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);

        $id = isset($data['id']) ? $data['id'] : $data['user_id'];
        $apiKey = isset($data['api_key']) ? $data['api_key'] : null;
        $email = isset($data['email']) ? $data['email'] : null;
        $apiKeys = [];
        if (isset($data['api_keys'])) {
            $apiKeys = array_map(function ($key) {
                return UserApiKey::createFromArray($key);
            }, $data['api_keys']);
        }

        return new self($id, $email, $apiKey, $apiKeys);
    }

    /**
     * Get Id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get Api Key.
     *
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return array<UserApiKey>
     */
    public function getApiKeys(): array
    {
        return $this->apiKeys;
    }
}

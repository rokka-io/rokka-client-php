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
     * @var string
     */
    public $email;

    /**
     * Key.
     *
     * @var string
     */
    public $apiKey;

    /**
     * Constructor.
     *
     * @param string $id     Id
     * @param string $email  Email
     * @param string $apiKey API key
     */
    public function __construct($id, $email, $apiKey)
    {
        $this->id = $id;
        $this->email = $email;
        $this->apiKey = $apiKey;
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

        return new self($data['id'], $data['email'], $data['api_key']);
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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get Api Key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}

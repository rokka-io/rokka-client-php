<?php

namespace Rokka\Client\Core;

/**
 * Organizations are independent instances of the Rokka service.
 *
 * Images and stacks are always managed in the context of an organization.
 *
 * Users can be part of multiple organizations.
 */
class Organization
{
    /**
     * UUID v4.
     *
     * @var string
     */
    public $id;

    /**
     * Public display name.
     *
     * @var string
     */
    public $displayName;

    /**
     * Organization name.
     *
     * Web safe, using in routes and api calls
     *
     * @var string
     */
    public $name;

    /**
     * Email.
     *
     * @var string
     */
    public $billingEmail;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $signing_keys = [];

    /**
     * Constructor.
     *
     * @param string $id           Id
     * @param string $name         Name, used in urls etc
     * @param string $displayName  Display name
     * @param string $billingEmail Email
     * @param array  $options
     * @param array  $signing_keys
     */
    public function __construct($id, $name, $displayName, $billingEmail, $signing_keys = [], $options = [])
    {
        $this->id = $id;
        $this->displayName = $displayName;
        $this->name = $name;
        $this->billingEmail = $billingEmail;
        $this->options = $options;
        $this->signing_keys = $signing_keys;
    }

    /**
     * Create an organization from the JSON data.
     *
     * @param string $jsonString JSON as a string
     *
     * @return self
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);

        return new self(
            $data['id'],
            $data['name'],
            $data['display_name'],
            $data['billing_email'],
            isset($data['signing_keys']) ? $data['signing_keys'] : [],
            isset($data['options']) ? $data['options'] : []);
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
     * Get name for displaying.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Get name for url.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Billing Email.
     *
     * @return string
     */
    public function getBillingEmail()
    {
        return $this->billingEmail;
    }

    /**
     * Get organization options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getSigningKeys()
    {
        return $this->signing_keys;
    }
}

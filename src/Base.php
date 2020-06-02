<?php

namespace Rokka\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Base Client class.
 */
abstract class Base
{
    const DEFAULT_API_BASE_URL = 'https://api.rokka.io';

    const DEFAULT_API_VERSION = 1;

    const API_KEY_HEADER = 'Api-Key';

    const API_VERSION_HEADER = 'Api-Version';

    /**
     * Client to access Rokka.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Default organization.
     *
     * @var string|null
     */
    protected $defaultOrganization;

    /**
     * @var int
     */
    private $apiVersion = self::DEFAULT_API_VERSION;

    /**
     * Rokka credentials.
     *
     * @var array
     */
    private $credentials = [
        'key' => '',
    ];

    /**
     * Constructor.
     *
     * @param ClientInterface $client              Client instance
     * @param string|null     $defaultOrganization
     */
    public function __construct(ClientInterface $client, $defaultOrganization)
    {
        $this->defaultOrganization = $defaultOrganization;

        $this->client = $client;
    }

    /**
     * Set the credentials.
     *
     * @param string $key API key
     *
     * @return void
     */
    public function setCredentials($key)
    {
        $this->credentials = ['key' => $key];
    }

    /**
     * Call the API rokka endpoint.
     *
     * @param string $method           HTTP method to use
     * @param string $path             Path on the API
     * @param array  $options          Request options
     * @param bool   $needsCredentials True if credentials are needed
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    protected function call($method, $path, array $options = [], $needsCredentials = true)
    {
        $options['headers'][self::API_VERSION_HEADER] = $this->apiVersion;

        if ($needsCredentials) {
            $options['headers'][self::API_KEY_HEADER] = $this->credentials['key'];
        }

        return $this->client->request($method, $path, $options);
    }

    /**
     * Return the organization or the default if empty.
     *
     * @param string|null $organization Organization
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getOrganizationName($organization = null)
    {
        $org = (empty($organization)) ? $this->defaultOrganization : $organization;
        if (null === $org) {
            throw new \RuntimeException('Organization is empty');
        }

        return $org;
    }
}

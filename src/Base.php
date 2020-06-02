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

    public function __construct(ClientInterface $client, ?string $defaultOrganization)
    {
        $this->client = $client;
        $this->defaultOrganization = $defaultOrganization;
    }

    /**
     * Set the API key as credentials.
     */
    public function setCredentials(string $key)
    {
        $this->credentials = ['key' => $key];
    }

    /**
     * Call the API rokka endpoint.
     *
     * @throws GuzzleException
     */
    protected function call(string $method, string $path, array $options = [], bool $needsCredentials = true): ResponseInterface
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
     * @throws \RuntimeException
     */
    protected function getOrganizationName(?string $organization = null): string
    {
        $org = $organization ?: $this->defaultOrganization;
        if (null === $org) {
            throw new \RuntimeException('Organization is empty');
        }

        return $org;
    }
}

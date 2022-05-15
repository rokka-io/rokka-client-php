<?php

namespace Rokka\Client;

use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Base Client class.
 */
abstract class Base
{
    public const DEFAULT_API_BASE_URL = 'https://api.rokka.io';

    protected const DEFAULT_API_VERSION = 1;

    protected const API_KEY_HEADER = 'Api-Key';

    protected const API_AUTHORIZATION_HEADER = 'Authorization';

    protected const API_VERSION_HEADER = 'Api-Version';

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
        'token' => null,
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
        $this->credentials['key'] = $key;
    }

    /**
     * Set the API Token.
     *
     * @param string|null $token API token
     *
     * @return void
     */
    public function setToken(?string $token)
    {
        $this->credentials['token'] = $token;
    }

    /**
     * Get the API Token.
     */
    public function getToken(): ?string
    {
        return $this->credentials['token'];
    }

    /**
     * Get the API Token Payload (unverified).
     */
    public function getTokenPayload(?string $token = null): ?array
    {
        if (null === $token) {
            $token = $this->getToken();
        }
        if (!$token) {
            return null;
        }

        return $this->getUnvalidatedPayload($token);
    }

    public function getTokenIsValidFor(?string $token = null): int
    {
        $payload = $this->getTokenPayload($token);
        if (null === $payload) {
            return -1;
        }
        if (!isset($payload['exp'])) {
            return -1;
        }

        return $payload['exp'] - time();
    }

    /**
     * Call the API rokka endpoint.
     *
     * @param string $method           HTTP method to use
     * @param string $path             Path on the API
     * @param array  $options          Request options
     * @param bool   $needsCredentials True if credentials are needed
     * @param array  $credentials      Credentials to be used, useful for overwriting api-key
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    protected function call($method, $path, array $options = [], $needsCredentials = true, $credentials = [])
    {
        $options['headers'][self::API_VERSION_HEADER] = $this->apiVersion;

        if ($needsCredentials) {
            $token = $credentials['token'] ?? $this->credentials['token'];
            if (\is_string($token)) {
                $options['headers'][self::API_AUTHORIZATION_HEADER] = 'Bearer '.$token;
            } else {
                $options['headers'][self::API_KEY_HEADER] = $credentials['key'] ?? $this->credentials['key'];
            }
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

    protected function getUnvalidatedPayload(string $token): ?array
    {
        // try to get the user id from the token without actually validating it, because the key is in the userApiKey

        $tks = explode('.', $token);
        if (3 != \count($tks)) {
            return null;
        }
        $payloadRaw = JWT::urlsafeB64Decode($tks[1]);
        if (null === ($payload = JWT::jsonDecode($payloadRaw))) {
            return null;
        }

        $enc = json_encode($payload);
        if (false === $enc) {
            return null;
        }

        return json_decode($enc, true);
    }
}

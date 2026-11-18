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
     * @param string|null $key API key
     */
    public function setCredentials($key): void
    {
        $this->credentials['key'] = $key;
    }

    /**
     * Set the API Token.
     *
     * @since 1.17.0
     *
     * @param string|null $token API token
     */
    public function setToken(?string $token): void
    {
        $this->credentials['token'] = $token;
    }

    /**
     * Get the API Token.
     *
     * @since 1.17.0
     */
    public function getToken(): ?string
    {
        return $this->credentials['token'];
    }

    /**
     * Get the API Token Payload (unverified).
     *
     * @since 1.17.0
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

    /**
     * Returns for how many seconds a token is valid for.
     *
     * Doesn't check for other validity (like ip restrictions)
     *
     * Returns -1 if there's no token
     *
     * @since 1.17.0
     */
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
            $key = $credentials['key'] ?? $this->credentials['key'];
            if (\is_string($key) && '' !== $key) {
                $options['headers'][self::API_KEY_HEADER] = $credentials['key'] ?? $this->credentials['key'];
            } else {
                $token = $credentials['token'] ?? $this->credentials['token'];
                $options['headers'][self::API_AUTHORIZATION_HEADER] = 'Bearer '.$token;
            }
        }

        return $this->client->request($method, $path, $options);
    }

    /**
     * Return the organization or the default if empty.
     *
     * @param string|null $organization Organization
     *
     * @throws \RuntimeException         If no organization is given and there is no default
     * @throws \InvalidArgumentException If the organization name is invalid
     *
     * @return string
     */
    protected function getOrganizationName($organization = null)
    {
        $org = (empty($organization)) ? $this->defaultOrganization : $organization;
        if (null === $org) {
            throw new \RuntimeException('Organization is empty');
        }

        return self::validateOrganizationName($org);
    }

    /**
     * Ensure an organization name only contains [a-z0-9_-].
     *
     * Organization names end up in API paths and in render hostnames, so anything else
     * (e.g. "../users", "org?x=1", "evil.com/") is rejected instead of being encoded.
     *
     * @throws \InvalidArgumentException If the organization name is invalid
     */
    protected static function validateOrganizationName(string $organization): string
    {
        if (!preg_match('/^[a-z0-9_-]+$/', $organization)) {
            throw new \InvalidArgumentException(\sprintf('Invalid organization name "%s", only a-z, 0-9, "-" and "_" are allowed', $organization));
        }

        return $organization;
    }

    /**
     * Encode a value for use as a single API path segment.
     *
     * Prevents values like "../stacks/org/name", ".." or "abc?x=1" from changing the requested
     * endpoint, since Guzzle resolves dot segments in relative paths against the base URI.
     * Empty, "." and ".." segments are rejected: rawurlencode leaves dots alone, and even a
     * percent-encoded "%2E%2E" may be normalized back into a dot segment by proxies.
     *
     * @throws \InvalidArgumentException If the segment is empty, "." or ".."
     */
    protected static function encodePathSegment(string|int $segment): string
    {
        $segment = (string) $segment;
        if ('' === $segment || '.' === $segment || '..' === $segment) {
            throw new \InvalidArgumentException(\sprintf('Invalid API path segment "%s"', $segment));
        }

        return rawurlencode($segment);
    }

    /**
     * Gets the payload of an API JWT Token (not validated against the signature).
     */
    protected function getUnvalidatedPayload(string $token): ?array
    {
        // try to get the user id from the token without actually validating it, because the key is in the userApiKey

        $tks = explode('.', $token);
        if (3 != \count($tks)) {
            return null;
        }
        $payloadRaw = base64_decode(strtr($tks[1], '-_', '+/'), true);
        if (false === $payloadRaw) {
            return null;
        }

        $payload = json_decode($payloadRaw, true);
        if (!\is_array($payload)) {
            return null;
        }

        return $payload;
    }
}

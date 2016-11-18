<?php

namespace Rokka\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

/**
 * Base Client class
 */
abstract class Base
{
    const DEFAULT_API_BASE_URL = 'https://api.rokka.io';
    const DEFAULT_API_VERSION = 1;

    const API_KEY_HEADER = 'Api-Key';
    const API_VERSION_HEADER = 'Api-Version';

    /**
     * @var integer
     */
    private $apiVersion = self::DEFAULT_API_VERSION;

    /**
     * Client to access Rokka
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Rokka credentials.
     *
     * @var array
     */
    private $credentials = [
        'key'       => '',
        'secret' => '',
    ];

    /**
     * Constructor.
     *
     * @param ClientInterface $client Client instance
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Set the credentials.
     *
     * @param string $key    API key
     * @param string $secret API secret
     *
     * @return void
     */
    public function setCredentials($key, $secret)
    {
        $this->credentials = ['key' => $key, 'secret' => $secret];
    }

    /**
     * Call the API rokka endpoint.
     *
     * @param string  $method           HTTP method to use
     * @param string  $path             Path on the API
     * @param array   $options          Request options
     * @param boolean $needsCredentials True if credentials are needed
     * @param int     $retryCount       How many times did we retry this
     *
     * @return Response
     */
    protected function call($method, $path, array $options = [], $needsCredentials = true, $retryCount = 0)
    {
        $options['headers'][self::API_VERSION_HEADER] = $this->apiVersion;

        if ($needsCredentials) {
            $options['headers'][self::API_KEY_HEADER] = $this->credentials['key'];
        }

        try {
            return $this->client->request($method, $path, $options);
        } catch (ClientException $e) {
            /* if the server responded with a 429 Too Many Requests
             * retry for max. 10 times and wait longer with each time
             * Accounts to total 110 seconds
             */
            if ($e->getCode() == 429 && $retryCount < 10) {
                $retryCount++;
                sleep($retryCount * 2);
                return $this->call($method, $path, $options, $needsCredentials, $retryCount);
            }
            throw $e;
        }
    }
}

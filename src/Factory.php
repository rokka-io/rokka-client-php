<?php

namespace Rokka\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Rokka\Client\Base as BaseClient;
use Rokka\Client\Image as ImageClient;
use Rokka\Client\User as UserClient;

/**
 * Factory class with static methods to easily instantiate clients.
 */
class Factory
{
    const API_BASE_URL = 'api_base_url';

    const PROXY = 'proxy';

    const GUZZLE_OPTIONS = 'guzzle_options';

    /**
     * Return an image client.
     *
     * @param string       $organization Organization name
     * @param string       $apiKey       API key
     * @param array|string $options      Options like api_base_url or proxy
     *
     * @throws \RuntimeException
     *
     * @return Image
     */
    public static function getImageClient($organization, $apiKey, $options = [])
    {
        $baseUrl = BaseClient::DEFAULT_API_BASE_URL;
        if (!\is_array($options)) { // $options was introduced later, if that is an array, we're on the new sig, nothing to change
            if (\func_num_args() > 3) { // if more than 3 args, the 4th is the baseUrl
                $baseUrl = func_get_arg(3);
            } elseif (3 === \func_num_args()) { // if exactly 3 args
                //if $baseUrl doesn't start with http, it may be a secret from the old signature, remove that, it's not used anymore
                if ('http' !== substr($options, 0, 4)) {
                    $baseUrl = BaseClient::DEFAULT_API_BASE_URL;
                } else {
                    $baseUrl = $options;
                }
            }
            $options = [];
        } else {
            if (isset($options[self::API_BASE_URL])) {
                $baseUrl = $options[self::API_BASE_URL];
            }
        }

        $client = self::getGuzzleClient($baseUrl, $options);

        return new ImageClient($client, $organization, $apiKey);
    }

    /**
     * Return a user client.
     *
     * @param string|null|array $organization
     * @param string|null       $apiKey       API key
     * @param array             $options      Options like api_base_url or proxy
     *
     * @throws \RuntimeException
     *
     * @return UserClient
     */
    public static function getUserClient($organization = null, $apiKey = null, $options = [])
    {
        $baseUrl = BaseClient::DEFAULT_API_BASE_URL;

        //bc compability, when first param was $options
        if (\is_array($organization)) {
            $options = $organization;
            $organization = null;
            $apiKey = null;
        }
        if (isset($options[self::API_BASE_URL])) {
            $baseUrl = $options[self::API_BASE_URL];
        }

        $client = self::getGuzzleClient($baseUrl, $options);

        return new UserClient($client, $organization, $apiKey);
    }

    /**
     * Returns a Guzzle client with a retry middleware.
     *
     * @param string $baseUrl base url
     * @param array  $options
     *
     * @throws \RuntimeException
     *
     * @return GuzzleClient GuzzleClient to connect to the backend
     */
    private static function getGuzzleClient($baseUrl, $options = [])
    {
        $guzzleOptions = [];
        if (isset($options[self::PROXY])) {
            $guzzleOptions[self::PROXY] = $options[self::PROXY];
        }
        if (isset($options[self::GUZZLE_OPTIONS])) {
            $guzzleOptions = array_merge($guzzleOptions, $options[self::GUZZLE_OPTIONS]);
        }

        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(Middleware::retry(self::retryDecider(), self::retryDelay()));
        $options = array_merge(['base_uri' => $baseUrl, 'handler' => $handlerStack], $guzzleOptions);

        return new GuzzleClient($options);
    }

    /**
     * Returns a Closure for the Retry Middleware to decide if it should retry the request when it failed.
     *
     * @return \Closure
     */
    private static function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // Limit the number of retries to 10
            if ($retries >= 10) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors or overload
                $statusCode = $response->getStatusCode();
                if (429 == $statusCode || 504 == $statusCode || 503 == $statusCode || 502 == $statusCode) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Returns a Closure for the Retry Middleware to tell it how long it should wait.
     *
     * @return \Closure
     */
    private static function retryDelay()
    {
        return function ($numberOfRetries) {
            return 2000 * $numberOfRetries;
        };
    }
}

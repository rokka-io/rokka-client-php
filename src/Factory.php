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
    /**
     * Return an image client.
     *
     * @param string $organization Organization name
     * @param string $apiKey       API key
     * @param string $baseUrl      Optional base url
     *
     * @return Image
     */
    public static function getImageClient($organization, $apiKey, $baseUrl = BaseClient::DEFAULT_API_BASE_URL)
    {
        if (func_num_args() > 3) {
            $baseUrl = func_get_arg(3);
        } elseif (3 === func_num_args()) {
            //if $baseUrl doesn't start with http, it may be a secret from the old signature, remove that, it's not used anymore
            if ('http' !== substr($baseUrl, 0, 4)) {
                $baseUrl = BaseClient::DEFAULT_API_BASE_URL;
            }
        }
        $client = self::getGuzzleClient($baseUrl);

        return new ImageClient($client, $organization, $apiKey);
    }

    /**
     * Return a user client.
     *
     * @param string $baseUrl Optional base url
     *
     * @return UserClient
     */
    public static function getUserClient($baseUrl = BaseClient::DEFAULT_API_BASE_URL)
    {
        $client = self::getGuzzleClient($baseUrl);

        return new UserClient($client);
    }

    /**
     * Returns a Guzzle client with a retry middleware.
     *
     * @param string $baseUrl base url
     *
     * @return GuzzleClient GuzzleClient to connect to the backend
     */
    private static function getGuzzleClient($baseUrl)
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(Middleware::retry(self::retryDecider(), self::retryDelay()));

        return new GuzzleClient(['base_uri' => $baseUrl, 'handler' => $handlerStack]);
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
                if (429 == $statusCode || 503 == $statusCode || 502 == $statusCode) {
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

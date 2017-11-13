<?php

namespace Rokka\Client;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\Stack;

class UriHelper
{
    /**
     * Allows you to add stack options to a Rokka URL.
     *
     * Useful eg. if you just want to add "options-dpr-2" to an existing URL.
     * Returns the original URL, if it can't parse it as valid Rokka URL.
     *
     * @param string $url     The rokka image render URL
     * @param string $options The options you want to add as string
     *
     * @return string
     */
    public static function addOptionsToUriString($url, $options)
    {
        return (string) self::addOptionsToUri(new Uri($url), $options);
    }

    /**
     * Allows you to add stack options to a Rokka URL.
     *
     * Useful eg. if you just want to add "options-dpr-2" to an existing URL
     * Returns the original URL, if it can't parse it as valid Rokka URL.
     *
     * @param UriInterface $uri     The rokka image render URL
     * @param string       $options The options you want to add as string
     *
     * @return UriInterface
     */
    public static function addOptionsToUri(UriInterface $uri, $options)
    {
        $path = $uri->getPath();
        if (preg_match('#^/(?<stack>[^/]+)/(?<rest>[0-9a-f]{6,40}.*)$#', $path, $matches)) {
            // nothing to do here
        } elseif (preg_match('#^/(?<stack>[^/]+)/(?<options>[^/]+)/(?<rest>[0-9a-f]{6,40}.*)$#', $path, $matches)) {
            $urlOptions = self::decomposeOptions($matches['options']);
            $inputOptions = self::decomposeOptions($options);
            $combinedOptions = array_replace_recursive($urlOptions, $inputOptions);
            $options = self::getUriStringFromStackConfig($combinedOptions);
        } else {
            //if nothing matches, it's not a proper rokka URL, just return the original uri
            return $uri;
        }

        return $uri->withPath('/'.$matches['stack'].'/'.$options.'/'.$matches['rest']);
    }

    /**
     * Returns a dynamic stack as string from a Stack object.
     *
     * Can be used to generate rokka render urls without having to save the stack on rokka.
     *
     * @since 1.1.0
     *
     * @param Stack $stack
     *
     * @return string
     */
    public static function getDynamicStackFromStackObject(Stack $stack)
    {
        return self::getUriStringFromStackConfig($stack->getConfigAsArray());
    }

    /**
     * Returns a dynamic stack as String from a Stack config.
     *
     * The array config looks like
     * ['operations' => ['resize' => ['width' => 500]],
     *  'options' => ['jpg.quality' => 80, 'autoformat' => true]
     * ]
     *
     * Expressions are not supported in dynamic stack urls
     *
     * Can be used to generate rokka render urls without having to save the stack on rokka.
     *
     * @since 1.1.0
     *
     * @param array $config
     *
     * @return string
     */
    public static function getDynamicStackFromStackConfig(array $config)
    {
        return self::getUriStringFromStackConfig($config);
    }

    /**
     * @param string $options
     *
     * @return array
     */
    private static function decomposeOptions($options)
    {
        $components = [];
        foreach (explode('--', $options) as $stringOperation) {
            $stringOperationWithOptions = explode('-', $stringOperation);
            $stringOperationName = $stringOperationWithOptions[0];
            if ('' == $stringOperationName) {
                continue;
            }
            $components[$stringOperationName] = self::parseOptions(array_slice($stringOperationWithOptions, 1));
        }

        return $components;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private static function parseOptions(array $options)
    {
        $optionValues = array_filter($options, function ($key) {
            return $key % 2;
        }, ARRAY_FILTER_USE_KEY);

        $optionKeys = array_filter($options, function ($key) {
            return !($key % 2);
        }, ARRAY_FILTER_USE_KEY);

        if (count($optionKeys) !== count($optionValues)) {
            throw new \InvalidArgumentException('The options given has to be an even array with key and value.');
        }

        return array_combine($optionKeys, $optionValues);
    }

    /**
     * @param $config
     *
     * @return string
     */
    private static function getUriStringFromStackConfig($config)
    {
        $newOptions = [];
        // move operations to root, in case they are in their separate key
        if (isset($config['operations'])) {
            foreach ($config['operations'] as $name => $operation) {
                $config[$name] = $operation;
            }
            unset($config['operations']);
        }
        $newStackOptions = null;
        foreach ($config as $key => $values) {
            // do options in the end
            $newOption = "$key";
            ksort($values);
            foreach ($values as $k => $v) {
                $newOption .= "-$k-$v";
            }
            if ('options' == $key) {
                $newStackOptions = $newOption;
            } else {
                $newOptions[] = $newOption;
            }
        }

        $options = implode('--', $newOptions);

        if (null !== $newStackOptions) {
            $options .= '--'.$newStackOptions;
        }

        return $options;
    }
}

<?php

namespace Rokka\Client;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

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
            $newOptions = [];
            foreach ($combinedOptions as $key => $values) {
                $newOption = "$key";
                foreach ($values as $k => $v) {
                    $newOption .= "-$k-$v";
                }
                $newOptions[] = $newOption;
            }
            $options = implode('--', $newOptions);
        } else {
            //if nothing matches, it's not a proper rokka URL, just return the original uri
            return $uri;
        }

        return $uri->withPath('/'.$matches['stack'].'/'.$options.'/'.$matches['rest']);
    }

    /**
     * @param string      $url
     * @param string      $size
     * @param null|string $custom
     *
     * @return UriInterface
     */
    public static function getSrcSetUrlString($url, $size, $custom = null)
    {
        return self::getSrcSetUrl($size, new Uri($url), $custom);
    }

    /**
     * @param string       $size
     * @param UriInterface $url
     * @param null|string  $custom
     *
     * @return UriInterface
     */
    public static function getSrcSetUrl($size, UriInterface $url, $custom = null)
    {
        $identifier = substr($size, -1, 1);
        $size = substr($size, 0, -1);
        if ('x' === $identifier) {
            $uri = self::addOptionsToUri($url, 'options-dpr-'.$size);
        } elseif ('w' === $identifier) {
            $uri = self::addOptionsToUri($url, 'resize-width-'.$size);
        } else {
            return $url;
        }
        if (null != $custom) {
            if (preg_match('#^([0-9]+)x$#', $custom, $matches)) {
                $uri = self::addOptionsToUri($uri, 'options-dpr-'.$matches[1].'--resize-width-'.(int) ceil($size / $matches[1]));
            } else {
                $options = self::decomposeOptions($custom);
                // if dpr is given in custom option, but not width, calculate correct width
                if (isset($options['options']['dpr']) && !isset($options['resize']['width'])) {
                    $custom .= '--resize-width-'.(int) ceil($size / $options['options']['dpr']);
                }

                $uri = self::addOptionsToUri($uri, $custom);
            }
        }

        return $uri;
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
}

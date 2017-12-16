<?php

namespace Rokka\Client;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackOperation;
use Rokka\Client\Core\StackUri;
use Rokka\Client\Core\StackUriComponents;

class UriHelper
{
    /**
     * Allows you to add stack options to a Rokka URL.
     *
     * Useful eg. if you just want to add "options-dpr-2" to an existing URL.
     * Returns the original URL, if it can't parse it as valid Rokka URL.
     *
     * @param string       $url     The rokka image render URL
     * @param array|string $options The options you want to add as string
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
     * @param array|string $options The options you want to add as string
     *
     * @return UriInterface
     */
    public static function addOptionsToUri(UriInterface $uri, $options)
    {
        if (is_array($options)) {
            return self::addOptionsToUri($uri, self::getUriStringFromStackConfig($options));
        }
        $matches = self::decomposeUri($uri);
        if (empty($matches)) {
            //if nothing matches, it's not a proper rokka URL, just return the original uri
            return $uri;
        }
        /** @var StackUri $stack */
        $stack = $matches['stack'];
        $stack->addOverridingOptions($options);

        return self::composeUri($matches, $uri);
    }

    /**
     * Generate a rokka uri with the array format returned by decomposeUri().
     *
     * The array config looks like
     * ['stack' => 'stackname', #or StackUrl object
     *  'hash' => 'hash',
     *  'filename' => 'filename-for-url'
     *  'format' => 'image format' # eg. jpg
     *  'stack' => StackUrl object with options and operations # same methods as a Stack objects
     * ]
     *
     * @since 1.2.0
     *
     * @param array|StackUriComponents $components
     * @param UriInterface             $uri        If this is provided, it will change the path for that object
     *
     * @return UriInterface
     */
    public static function composeUri($components, UriInterface $uri = null)
    {
        if (is_array($components)) {
            $components = StackUriComponents::createFromArray($components);
        }
        $stack = $components->getStack();
        $stackName = $stack->getName();
        $path = '/'.$stackName;
        $stackConfig = $stack->getConfigAsArray();
        if ('dynamic' === $stackName) {
            $path .= '/'.self::getUriStringFromStackConfig($stackConfig);
        } else {
            $stackUrl = self::getUriStringFromStackConfig($stackConfig);
            if (!empty($stackUrl)) {
                $path .= '/'.$stackUrl;
            }
        }
        if (!empty($components->getHash())) {
            $path .= '/'.$components->getHash();

            if (!empty($components->getFilename())) {
                $path .= '/'.$components->getFilename();
            }

            $path .= '.'.$components->getFormat();
        }

        if (null !== $uri) {
            return  $uri->withPath($path);
        }

        return new Uri($path);
    }

    /**
     * Return components of a rokka URL.
     *
     * @since 1.2.0
     *
     * @param UriInterface $uri
     *
     * @return StackUriComponents|null
     */
    public static function decomposeUri(UriInterface $uri)
    {
        $path = $uri->getPath();
        if (preg_match('#^/(?<stack>[^/]+)/(?<hash>[0-9a-f]{6,40})(?<rest>.*)$#', $path, $matches)) {
        } elseif (preg_match('#^/(?<stack>[^/]+)/(?<combinedOptions>.+)/(?<hash>[0-9a-f]{6,40})(?<rest>.*)$#', $path, $matches)) {
        }
        if (0 === count($matches)) {
            return null;
        }
        if (preg_match('#^/{0,1}(?<filename>[a-z\-\0-\9]*)\.(?<format>.{3,4}$)#', $matches['rest'], $matches2)) {
            $matches = array_merge($matches, $matches2);
        }
        $stack = new StackUri($matches['stack']);

        if (isset($matches['combinedOptions'])) {
            $stack->addOverridingOptions($matches['combinedOptions']);
        }

        $matches['stack'] = $stack;
        $components = new StackUriComponents($stack, $matches['hash'], $matches['format']);
        if (!empty($matches['filename'])) {
            $components->setFilename($matches['filename']);
        }

        return $components;
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
        if (null !== $custom) {
            if (preg_match('#^([0-9]+)x$#', $custom, $matches)) {
                $uri = self::addOptionsToUri($uri, 'options-dpr-'.$matches[1].'--resize-width-'.(int) ceil($size / $matches[1]));
            } else {
                $stack = new StackUri();
                $stack->addOverridingOptions($custom);
                // if dpr is given in custom option, but not width, calculate correct width
                $resizeOperations = $stack->getStackOperationsByName('resize');
                $widthIsNotSet = true;
                foreach ($resizeOperations as $resizeOperation) {
                    if (isset($resizeOperation->options['width'])) {
                        $widthIsNotSet = false;
                    }
                }
                $options = $stack->getStackOptions();
                if (isset($options['dpr']) && $widthIsNotSet) {
                    $custom .= '--resize-width-'.(int) ceil($size / $options['dpr']);
                }

                $uri = self::addOptionsToUri($uri, $custom);
            }
        }

        return $uri;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private static function getUriStringFromStackConfig(array $config)
    {
        $newOptions = [];
        if (isset($config['operations'])) {
            foreach ($config['operations'] as $values) {
                if ($values instanceof StackOperation) {
                    $newOptions[] = self::getStringForOptions($values->name, $values->options);
                } else {
                    $newOptions[] = self::getStringForOptions($values['name'], $values['options']);
                }
            }
        }

        $newStackOptions = null;
        if (isset($config['options'])) {
            $newStackOptions = self::getStringForOptions('options', $config['options']);
        }
        //don't return this, if it's only "options" as string
        if ('options' !== $newStackOptions) {
            $newOptions[] = $newStackOptions;
        }
        $options = implode('--', $newOptions);

        return $options;
    }

    /**
     * @param string $name
     * @param array  $values
     *
     * @return string
     */
    private static function getStringForOptions($name, $values)
    {
        $newOption = $name;
        ksort($values);
        foreach ($values as $k => $v) {
            if (false === $v) {
                $v = 'false';
            } elseif (true === $v) {
                $v = 'true';
            }
            $newOption .= "-$k-$v";
        }

        return $newOption;
    }
}

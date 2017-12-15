<?php

namespace Rokka\Client;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackOperation;
use Rokka\Client\Core\StackUrl;

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
        $matches['stack'] = self::addOptionsToStackUrlObject($matches['stack'], $options);

        return self::composeUri($matches, $uri);
    }

    /**
     * Generate a rokka uri with the array format returned by decomposeUri().
     *
     * The array config looks like
     * ['stack' => 'stackname',
     *  'hash' => 'hash',
     *  'filename' => 'filename-for-url'
     *  'format' => 'image format' # eg. jpg
     *  'stack' => StackUrl object with options and operations # same methods as a Stack objects
     * ]
     *
     * @since 1.2.0
     *
     * @param array        $components
     * @param UriInterface $uri        If this is provided, it will change the path for that object
     *
     * @return UriInterface
     */
    public static function composeUri(array $components, UriInterface $uri = null)
    {
        /** @var StackUrl $stack */
        $stack = $components['stack'];
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
        if (isset($components['hash']) && !empty($components['hash'])) {
            $path .= '/'.$components['hash'];

            if (isset($components['filename']) && !empty($components['filename'])) {
                $path .= '/'.$components['filename'];
            }

            $path .= '.'.$components['format'];
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
     * @return array
     */
    public static function decomposeUri(UriInterface $uri)
    {
        $path = $uri->getPath();
        if (preg_match('#^/(?<stack>[^/]+)/(?<hash>[0-9a-f]{6,40})(?<rest>.*)$#', $path, $matches)) {
        } elseif (preg_match('#^/(?<stack>[^/]+)/(?<combinedOptions>.+)/(?<hash>[0-9a-f]{6,40})(?<rest>.*)$#', $path, $matches)) {
        }
        if (0 === count($matches)) {
            return [];
        }
        if (preg_match('#^/{0,1}(?<filename>[a-z\-\0-\9]*)\.(?<format>.{3,4}$)#', $matches['rest'], $matches2)) {
            unset($matches['rest']);
            $matches = array_merge($matches, $matches2);
            if (empty($matches['filename'])) {
                $matches['filename'] = null;
            }
        }
        $stack = new StackUrl(null, $matches['stack']);
        if (isset($matches['combinedOptions'])) {
            $stack = self::addOptionsToStackUrlObject($stack, $matches['combinedOptions']);
            unset($matches['combinedOptions']);
        }
        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }
        $matches['stack'] = $stack;

        return $matches;
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
                $stack = self::addOptionsToStackUrlObject(new StackUrl(), $custom);
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
     * @param StackUrl $stack
     * @param string   $options
     *
     * @return StackUrl
     */
    private static function addOptionsToStackUrlObject(StackUrl $stack, $options)
    {
        $part = 0;
        // if stack already has operations we assume we don't want to add more, it's just overriding parameters
        if (count($stack->getStackOperations()) > 0) {
            ++$part;
        }
        foreach (explode('/', $options) as $option) {
            ++$part;
            foreach (explode('--', $option) as $stringOperation) {
                $stringOperationWithOptions = explode('-', $stringOperation);
                $stringOperationName = $stringOperationWithOptions[0];
                if ('' == $stringOperationName) {
                    continue;
                }
                $parsedOptions = self::parseOptions(array_slice($stringOperationWithOptions, 1));
                if ('options' === $stringOperationName) {
                    $stack->setStackOptions(array_merge($stack->getStackOptions(), $parsedOptions));
                } else {
                    // only add as stack operation everything before the first /
                    if (1 === $part) {
                        $stackOperation = new StackOperation($stringOperationName, $parsedOptions);
                        $stack->addStackOperation($stackOperation);
                    } else {
                        $stackOperations = $stack->getStackOperationsByName($stringOperationName);
                        foreach ($stackOperations as $stackOperation) {
                            $stackOperation->options = array_merge($stackOperation->options, $parsedOptions);
                        }
                    }
                }
            }
        }

        return $stack;
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

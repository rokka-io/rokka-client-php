<?php

namespace Rokka\Client;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\StackOperation;
use Rokka\Client\Core\StackUri;
use Rokka\Client\Core\UriComponents;

class UriHelper
{
    /**
     * Generate a rokka uri with an array or an UriComponent returned by decomposeUri().
     *
     * The array looks like
     * ```
     * ['stack' => 'stackname', #or StackUrl object
     *  'hash' => 'hash',
     *  'filename' => 'filename-for-url'
     *  'format' => 'image format' # eg. jpg
     *  'stack' => StackUrl object with options and operations # same methods as a Stack objects
     * ]
     * ```
     *
     * @since 1.2.0
     *
     * @param array|UriComponents $components
     * @param UriInterface        $uri        If this is provided, it will change the path for that object
     *
     * @return UriInterface
     */
    public static function composeUri($components, UriInterface $uri = null)
    {
        if (is_array($components)) {
            $components = UriComponents::createFromArray($components);
        }
        $stack = $components->getStack();
        $stackName = $stack->getName();
        $path = '/'.$stackName;
        $stackConfig = $stack->getConfigAsArray();
        $stackUrl = self::getUriStringFromStackConfig($stackConfig);
        if (!empty($stackUrl)) {
            $path .= '/'.$stackUrl;
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
     * @return UriComponents|null
     */
    public static function decomposeUri(UriInterface $uri)
    {
        if (!preg_match('#^/(?<stack>.+)/(?<hash>[0-9a-f]{6,40})/{0,1}(?<filename>[A-Za-z\-\0-\9]*)\.(?<format>.{3,4}$)$#', $uri->getPath(), $matches)) {
            return null;
        }

        return UriComponents::createFromArray($matches);
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

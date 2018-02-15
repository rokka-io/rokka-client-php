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
        $stack = $matches->getStack();
        $stack->addOverridingOptions($options);

        return self::composeUri($matches, $uri);
    }

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
        $stackPattern = '(?<stack>.*([^-]|--)|-*)';
        $hashPattern = '(?<hash>[0-9a-f]{6,40})';
        $filenamePattern = '(?<filename>[A-Za-z\-\0-\9]+)';
        $formatPattern = '(?<format>.{3,4})';
        $pathPattern = '(?<hash>-.+-)';

        if (preg_match('#^/'.$stackPattern.'/'.$hashPattern.'/'.$filenamePattern.'\.'.$formatPattern.'$#', $uri->getPath(), $matches)) {
            // hash with seo-filename
        } elseif (preg_match('#^/'.$stackPattern.'/'.$hashPattern.'.'.$formatPattern.'$#', $uri->getPath(), $matches)) {
            // hash without seo-filename
        } elseif (preg_match('#^/'.$stackPattern.'/'.$pathPattern.'/'.$filenamePattern.'\.'.$formatPattern.'$#', $uri->getPath(), $matches)) {
            // remote_path with seo-filename
        } elseif (preg_match('#^/'.$stackPattern.'/'.$pathPattern.'.'.$formatPattern.'$#', $uri->getPath(), $matches)) {
            // remote_path without seo-filename
        } else {
            return null;
        }

        return UriComponents::createFromArray($matches);
    }

    /**
     * @param string $url The original rokka render URL to be adjusted
     * @param string $size The size of the image, eg '300w' or '2x'
     * @param null|string $custom Any rokka options you'd like to add, or are a dpi identifier like '2x'
     *
     * @return UriInterface
     */
    public static function getSrcSetUrlString($url, $size, $custom = null)
    {
        return self::getSrcSetUrl(new Uri($url), $size, $custom);
    }

    /**
     * Returns a rokka URL to be used in srcset style attributes.
     *
     * $size can be eg. "2x" or "500w"
     * $custom can be any rokka options you want to optionally add, or also a dpi identifier like "2x"
     *
     * This method will then generate the right rokka URLs to get what you want, see
     * `\Rokka\Client\Tests\UriHelperTest::provideGetSrcSetUrl` for some examples and the expected returns.
     *
     * @param UriInterface $url The original rokka render URL to be adjusted
     * @param string $size The size of the image, eg '300w' or '2x'
     * @param null|string $custom Any rokka options you'd like to add, or are a dpi identifier like '2x'
     *
     * @return UriInterface
     */
    public static function getSrcSetUrl(UriInterface $url, $size, $custom = null)
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

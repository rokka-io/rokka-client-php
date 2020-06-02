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
     * @see UriHelper::addOptionsToUri
     *
     * @param string       $url        The rokka image render URL
     * @param array|string $options    The options you want to add as string
     * @param bool         $shortNames if short names (like o for option or v for variables) should be used
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function addOptionsToUriString($url, $options, $shortNames = true)
    {
        return (string) self::addOptionsToUri(new Uri($url), $options, $shortNames);
    }

    /**
     * Allows you to add stack options to a Rokka URL.
     *
     * Useful eg. if you just want to add "options-dpr-2" to an existing URL
     * Returns the original URL, if it can't parse it as valid Rokka URL.
     *
     * Example with string as input
     *
     * ```language-php
     * UriHelper::addOptionsToUri($uri, 'options-dpr-2--resize-upscale-false');
     * ```
     *
     * Example with array
     *
     * ```language-php
     * UriHelper::addOptionsToUri($uri,
     *   [
     *    'options' => ['dpr' => 2],
     *    'operations' =>
     *      [
     *        [
     *          'name' => 'resize',
     *          'options' => ['upscale' => 'false']
     *        ],
     *      ],
     *   ]);
     *
     * ```
     *
     *
     * @param UriInterface $uri        The rokka image render URL
     * @param array|string $options    The options you want to add as string
     * @param bool         $shortNames if short names (like o for option or v for variables) should be used
     *
     * @throws \RuntimeException
     *
     * @return UriInterface
     */
    public static function addOptionsToUri(UriInterface $uri, $options, $shortNames = true)
    {
        if (\is_array($options)) {
            return self::addOptionsToUri($uri, self::getUriStringFromStackConfig($options, $shortNames), $shortNames);
        }
        $matches = self::decomposeUri($uri);
        if (empty($matches)) {
            //if nothing matches, it's not a proper rokka URL, just return the original uri
            return $uri;
        }
        $stack = $matches->getStack();
        $stack->addOverridingOptions($options);

        return self::composeUri($matches, $uri, $shortNames);
    }

    /**
     * Generate a rokka uri with an array or an UriComponent returned by decomposeUri().
     *
     * The array looks like
     * ```
     * ['stack' => 'stackname', #or StackUri object
     *  'hash' => 'hash',
     *  'filename' => 'filename-for-url',
     *  'format' => 'image format', # eg. jpg
     * ]
     * ```
     *
     * @since 1.2.0
     *
     * @param array|UriComponents $components
     * @param UriInterface        $uri        If this is provided, it will change the path for that object and return
     * @param bool                $shortNames if short names (like o for option or v for variables) should be used
     *
     * @throws \RuntimeException
     *
     * @return UriInterface
     */
    public static function composeUri($components, UriInterface $uri = null, $shortNames = true)
    {
        if (\is_array($components)) {
            $components = UriComponents::createFromArray($components);
        }
        $stack = $components->getStack();
        $stackName = $stack->getName();
        $path = '/'.$stackName;
        $stackConfig = $stack->getConfigAsArray();
        $stackUrl = self::getUriStringFromStackConfig($stackConfig, $shortNames);
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
     * @throws \RuntimeException
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
        $path = $uri->getPath();
        // hash with seo-filename
        if (preg_match('#^/'.$stackPattern.'/'.$hashPattern.'/'.$filenamePattern.'\.'.$formatPattern.'$#', $path, $matches) ||
            // hash without seo-filename
            preg_match('#^/'.$stackPattern.'/'.$hashPattern.'.'.$formatPattern.'$#', $path, $matches) ||
            // remote_path with seo-filename
            preg_match('#^/'.$stackPattern.'/'.$pathPattern.'/'.$filenamePattern.'\.'.$formatPattern.'$#', $path, $matches) ||
            // remote_path without seo-filename
            preg_match('#^/'.$stackPattern.'/'.$pathPattern.'.'.$formatPattern.'$#', $path, $matches)) {
            return UriComponents::createFromArray($matches);
        }

        return null;
    }

    /**
     * @param string      $url           The original rokka render URL to be adjusted
     * @param string      $size          The size of the image, eg '300w' or '2x'
     * @param null|string $custom        Any rokka options you'd like to add, or are a dpi identifier like '2x'
     * @param bool        $setWidthInUrl If false, don't set the width as stack operation option, we provide it in $custom, usually as parameter
     *
     * @throws \RuntimeException
     *
     * @return UriInterface
     */
    public static function getSrcSetUrlString($url, $size, $custom = null, $setWidthInUrl = true)
    {
        return self::getSrcSetUrl(new Uri($url), $size, $custom, $setWidthInUrl);
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
     * @param UriInterface $url           The original rokka render URL to be adjusted
     * @param string       $size          The size of the image, eg '300w' or '2x'
     * @param null|string  $custom        Any rokka options you'd like to add, or are a dpi identifier like '2x'
     * @param bool         $setWidthInUrl If false, don't set the width as stack operation option, we provide it in $custom, usually as parameter
     *
     * @throws \RuntimeException
     *
     * @return UriInterface
     */
    public static function getSrcSetUrl(UriInterface $url, $size, $custom = null, $setWidthInUrl = true)
    {
        $identifier = substr($size, -1, 1);
        $size = substr($size, 0, -1);
        switch ($identifier) {
            case 'x':
                $uri = self::addOptionsToUri($url, 'options-dpr-'.$size);

                break;
            case 'w':
                if ($setWidthInUrl) {
                    $uri = self::addOptionsToUri($url, 'resize-width-'.$size);
                } else {
                    $uri = $url;
                }

                break;
            default:
                return $url;
        }
        if (null !== $custom) {
            $uri = self::getSrcSetUrlCustom($size, $custom, $setWidthInUrl, $uri);
        }

        return $uri;
    }

    /**
     * @param array $config
     * @param bool  $shortNames if short names (like o for option or v for variables) should be used
     *
     * @return string
     */
    private static function getUriStringFromStackConfig(array $config, $shortNames = true)
    {
        $newOptions = [];

        if (isset($config['operations'])) {
            foreach ($config['operations'] as $values) {
                if ($values instanceof StackOperation) {
                    $newOptions[] = self::getStringForOptions($values->name, $values->options, $values->expressions);
                } else {
                    if (!isset($values['expressions'])) {
                        $values['expressions'] = [];
                    }
                    $newOptions[] = self::getStringForOptions($values['name'], $values['options'], $values['expressions']);
                }
            }
        }

        $newStackOptions = null;
        $nameOptions = $shortNames ? 'o' : 'options';
        if (isset($config['options'])) {
            $newStackOptions = self::getStringForOptions($nameOptions, $config['options']);
        }
        //don't return this, if it's only "options" as string
        if (null !== $newStackOptions && $nameOptions !== $newStackOptions) {
            $newOptions[] = $newStackOptions;
        }

        $newStackVariables = null;
        $nameVariables = $shortNames ? 'v' : 'variables';
        if (isset($config['variables'])) {
            $newStackVariables = self::getStringForOptions($nameVariables, $config['variables']);
        }
        //don't return this, if it's only "variables" as string
        if (null !== $newStackVariables && $nameVariables !== $newStackVariables) {
            $newOptions[] = $newStackVariables;
        }
        $options = implode('--', $newOptions);

        return $options;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $expressions
     *
     * @return string
     */
    private static function getStringForOptions($name, $options, $expressions = [])
    {
        $newOption = $name;
        foreach ($expressions as $key => $value) {
            $expressions[$key] = '['.$value.']';
        }
        $options = array_merge($options, $expressions);
        ksort($options);
        foreach ($options as $k => $v) {
            if (false === $v) {
                $v = 'false';
            } elseif (true === $v) {
                $v = 'true';
            }
            $newOption .= "-$k-$v";
        }

        return $newOption;
    }

    /**
     * Adds custom options to the URL.
     *
     * @param string       $size
     * @param string       $custom
     * @param bool         $setWidthInUrl
     * @param UriInterface $uri
     *
     * @throws \RuntimeException if stack configuration can't be parsed
     *
     * @return UriInterface
     */
    private static function getSrcSetUrlCustom($size, $custom, $setWidthInUrl, UriInterface $uri)
    {
        // if custom is eg '2x', add options-dpr-
        if (preg_match('#^([0-9]+)x$#', $custom, $matches)) {
            $uri = self::addOptionsToUri($uri, 'options-dpr-'.$matches[1].
                ($setWidthInUrl ? '--resize-width-'.(int) ceil($size / $matches[1]) : ''));
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
            if (isset($options['dpr']) && $widthIsNotSet && $setWidthInUrl) {
                $custom .= '--resize-width-'.(int) ceil($size / $options['dpr']);
            }

            $uri = self::addOptionsToUri($uri, $custom);
        }

        return $uri;
    }
}

<?php

namespace Rokka\Client\Core;

use Psr\Http\Message\UriInterface;
use Rokka\Client\UriHelper;

/**
 * This class is useful for working on stack URIs (dynamic or defined ones).
 * You can use almost all the operations you can use on a common stack object here as well, but also retrieve
 * such a stack as rokka render URL for later usage in templates or similar.
 *
 * Examples:
 *
 * ```language-php
 * $stackUri = new StackUri('someStackName');
 * $stackUri->addOverridingOptions('options-dpr-2');
 * echo $stackUri->getStackUriString();
 * ```
 *
 * @see \Rokka\Client\Core\Stack::getDynamicUriString()
 * @see \Rokka\Client\UriHelper::addOptionsToUri()
 * @see \Rokka\Client\UriHelper::addOptionsToUriString()
 * @since 1.2.0
 */
class StackUri extends AbstractStack
{
    /**
     * @var string|null
     */
    private $baseUrl;

    public function __construct($name = null, array $stackOperations = [], array $stackOptions = [], $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        parent::__construct($name, $stackOperations, $stackOptions);

        if (false !== strpos($name, '/')) {
            // Some part of a rokka URL can have // in it, but it means nothing, remove them here.
            $name = preg_replace('#/{2,}#', '/', $name);
            list($name, $options) = explode('/', $name, 2);
            $this->addOverridingOptions($options);
            $this->setName($name);
        }
    }

    public function __toString()
    {
        return $this->getStackUriString();
    }

    /**
     * Returns the stack uri in 'dynamic' notation.
     *
     * @since 1.2.0
     *
     * @return UriInterface
     */
    public function getStackUri()
    {
        return UriHelper::composeUri(['stack' => $this]);
    }

    /**
     * Returns the stack url part as it should be with "addOptionsToUrl" calls in 'dynamic' notation.
     *
     * @since 1.2.0
     *
     * @return string
     */
    public function getStackUriString()
    {
        return trim(UriHelper::composeUri(['stack' => $this])->getPath(), '/');
    }

    /**
     * Gets stack operations / options as "flat" array.
     *
     * Useful for generating dynamic stacks for example
     *
     * @since 1.2.0
     *
     * @return array
     */
    public function getConfigAsArray()
    {
        $config = ['operations' => []];
        foreach ($this->getStackOperations() as $operation) {
            $config['operations'][] = $operation->toArray();
        }
        $config['options'] = $this->getStackOptions();

        return $config;
    }

    /**
     * For overwriting stack operation options or adding stack options.
     *
     * The format of the $options parameter is the same as you would use for overwriting ooptions via a render URL.
     *
     * Example: 'resize-width-200--options-dpr-2-autoformat-true'
     *
     * Using '/' instead of '--' is also valid, but if the object doesn't have operations defined already, the behaviour
     * is different
     * Examples:
     *
     * 'resize-width-200--crop-width-200-height-200' <- resizes and crops and image
     * 'resize-width-200/crop-width-200-height-200' <- only resized the image, since the crop is an overwrite and the operation doesnt exist
     *
     * But if there's already stack operations for resize and crop defined in the object, both above examples do the
     * same.
     *
     * @see https://rokka.io/documentation/references/render.html#overwriting-stack-operation-options
     * @since 1.2.0
     *
     * @param string $options The same format as overwriting stack operations options via url
     *
     * @return StackUri
     */
    public function addOverridingOptions($options)
    {
        $part = 0;
        // if stack already has operations we assume we don't want to add more, it's just overriding parameters
        if (count($this->getStackOperations()) > 0) {
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
                    $this->setStackOptions(array_merge($this->getStackOptions(), $parsedOptions));
                } else {
                    // only add as stack operation everything before the first /
                    if (1 === $part) {
                        $stackOperation = new StackOperation($stringOperationName, $parsedOptions);
                        $this->addStackOperation($stackOperation);
                    } else {
                        $stackOperations = $this->getStackOperationsByName($stringOperationName);
                        foreach ($stackOperations as $stackOperation) {
                            $stackOperation->options = array_merge($stackOperation->options, $parsedOptions);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array $options
     *
     * @throws \InvalidArgumentException
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

<?php

namespace Rokka\Client\Core;

use Rokka\Client\UriHelper;

class StackUri extends StackAbstract
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
      return $this->getStackUri();
    }

    /**
     * Returns the stack url part as it should be with "addOptionsToUrl" calls.
     *
     * @since 1.2.0
     *
     * @return string
     */
    public function getStackUri()
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
     * @since 1.2.0
     *
     * @param string $options
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

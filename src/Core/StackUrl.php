<?php

namespace Rokka\Client\Core;

use Rokka\Client\UriHelper;

class StackUrl extends Stack
{
    /**
     * @var string|null
     */
    private $baseUrl;

    public function __construct($name = null, array $stackOperations = [], array $stackOptions = [], $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        parent::__construct(null, $name, $stackOperations, $stackOptions);

        if (strpos($name, '/') !== false) {
            list ($name, $options) = explode('/',$name,2);
            $this->addOptions($options);
            $this->setName($name);
        }
    }

    /**
     * Returns the stack url part as it should be with "addOptionsToUrl" calls.
     *
     * @since 1.2.0
     *
     * @return string
     */
    public function getStackUrl()
    {
        return trim(UriHelper::composeUri(['stack' => $this])->getPath(), '/');
    }

    /**
     * @since 1.2.0
     *
     * @param string $options
     *
     * @return StackUrl
     */
    public function addOptions($options)
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

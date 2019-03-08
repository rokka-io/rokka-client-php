<?php

namespace Rokka\Client\Core;

/**
 * Represents an operation with configuration.
 */
class StackOperation
{
    /**
     * Name of the operation.
     *
     * @var string
     */
    public $name;

    /**
     * Configured operation options provided for the stack.
     *
     * @var array
     */
    public $options = [];

    /**
     * Configured operation expressions provided for the stack.
     *
     * @var array
     */
    public $expressions = [];

    /**
     * Constructor.
     *
     * @param string $name    Operation name
     * @param array  $options Optional options for the operation
     */
    public function __construct($name, array $options = [], array $expressions = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->expressions = $expressions;
    }

    /**
     * Return the stack operation as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'options' => $this->options,
            'expressions' => $this->expressions,
        ];
    }

    /**
     * Create a stack operation from the decoded JSON data returned by the rokka.io API.
     *
     * @param array $data Decoded JSON data
     *
     * @return StackOperation
     */
    public static function createFromDecodedJsonResponse($data)
    {
        if (!isset($data['expressions'])) {
            $data['expressions'] = [];
        }

        return new self($data['name'], $data['options'], $data['expressions']);
    }
}

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
     * Configured options provided for the stack.
     *
     * @var array
     */
    public $options = [];

    /**
     * Constructor.
     *
     * @param string $name    Operation name
     * @param array  $options Optional options for the operation
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
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
        ];
    }

    /**
     * Create a stack operation from the JSON data returned by the rokka.io API.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return StackOperation
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray && !is_array($data)) {
            $data = json_decode($data, true);
        }

        return new self($data['name'], $data['options']);
    }
}

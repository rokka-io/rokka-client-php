<?php

namespace Rokka\Client\Core;

/**
 * Represents a collection of image transformation operations in a stack.
 */
class OperationCollection implements \Countable, \Iterator
{
    /**
     * Array of operations.
     *
     * @var Operation[]
     */
    private $operations = [];

    /**
     * @var int
     */
    private $current = 0;

    /**
     * Constructor.
     *
     * @param Operation[] $operations Array of operations
     */
    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    /**
     * Return number of operations.
     */
    public function count(): int
    {
        return \count($this->operations);
    }

    /**
     * Return operations.
     *
     * @return Operation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Create a collection from the JSON data returned by the rokka.io API.
     *
     * @param string $jsonString JSON as a string
     *
     * @return OperationCollection
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);

        $operations = [];

        foreach ($data as $name => $operationData) {
            // Ensuring that the required fields exist.
            $operationData = array_merge(['required' => [], 'properties' => []], $operationData);
            $operations[] = new Operation($name, $operationData['properties'], $operationData['required']);
        }

        return new self($operations);
    }

    /**
     * @return Operation
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->operations[$this->current];
    }

    public function next(): void
    {
        ++$this->current;
    }

    public function key(): int
    {
        return $this->current;
    }

    public function valid(): bool
    {
        return $this->current < \count($this->operations);
    }

    public function rewind(): void
    {
        $this->current = 0;
    }
}

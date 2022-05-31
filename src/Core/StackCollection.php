<?php

namespace Rokka\Client\Core;

/**
 * Holds a list of stacks.
 */
class StackCollection implements \Countable, \Iterator
{
    /**
     * @var Stack[]
     */
    private $stacks = [];

    /**
     * @var int
     */
    private $current = 0;

    /**
     * Constructor.
     *
     * @param array $stacks Array of stacks
     */
    public function __construct(array $stacks)
    {
        foreach ($stacks as $stack) {
            if (!($stack instanceof Stack)) {
                throw new \LogicException('You can only use Stack inside StackCollection');
            }
        }

        $this->stacks = $stacks;
    }

    /**
     * Return the number of stacks in this collection.
     */
    public function count(): int
    {
        return \count($this->stacks);
    }

    /**
     * Return the stacks.
     *
     * @return Stack[]
     */
    public function getStacks()
    {
        return $this->stacks;
    }

    /**
     * Create a stack from the JSON data returned by the rokka.io API.
     *
     * @param string $data JSON data
     *
     * @return StackCollection
     */
    public static function createFromJsonResponse($data)
    {
        $data = json_decode($data, true);

        $stacks = array_map(function ($stack) {
            return Stack::createFromDecodedJsonResponse($stack);
        }, $data['items']);

        return new self($stacks);
    }

    /**
     * @return Stack
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->stacks[$this->current];
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
        return $this->current < \count($this->stacks);
    }

    public function rewind(): void
    {
        $this->current = 0;
    }
}

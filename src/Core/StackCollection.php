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
     *
     * @return int
     */
    public function count()
    {
        return count($this->stacks);
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
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return StackCollection
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }

        $stacks = array_map(function ($stack) {
            return Stack::createFromJsonResponse($stack, true);
        }, $data['items']);

        return new self($stacks);
    }

    public function current()
    {
        return $this->stacks[$this->current];
    }

    public function next()
    {
        ++$this->current;
    }

    public function key()
    {
        return $this->current;
    }

    public function valid()
    {
        return $this->current < count($this->stacks);
    }

    public function rewind()
    {
        $this->current = 0;
    }
}

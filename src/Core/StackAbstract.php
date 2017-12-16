<?php

namespace Rokka\Client\Core;

abstract class StackAbstract
{
    /**
     * @var string|null Name of the stack
     */
    public $name;

    /**
     * @var StackOperation[] Collection of stack operations that this stack has
     */
    public $stackOperations;

    /**
     * @var array Array of stack options that this stack has
     */
    public $stackOptions;

    public function __construct($name = null, array $stackOperations = [], array $stackOptions = [])
    {
        $this->name = $name;
        $this->stackOperations = $stackOperations;
        $this->stackOptions = $stackOptions;
    }

    /**
     * Get name of stack for url.
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @since 1.1.0
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return StackOperation[]
     */
    public function getStackOperations()
    {
        return $this->stackOperations;
    }

    /**
     * Returns all operations matching name.
     *
     * @since 1.2.0
     *
     * @param string $name operation name
     *
     * @return StackOperation[]
     */
    public function getStackOperationsByName($name)
    {
        $stackOperations = [];
        foreach ($this->stackOperations as $stackOperation) {
            if ($stackOperation->name === $name) {
                $stackOperations[] = $stackOperation;
            }
        }

        return $stackOperations;
    }

    /**
     * @since 1.1.0
     *
     * @param StackOperation[] $operations
     *
     * @return self
     */
    public function setStackOperations(array $operations)
    {
        $this->stackOperations = [];
        foreach ($operations as $operation) {
            $this->addStackOperation($operation);
        }

        return $this;
    }

    /**
     * Adds a StackOperation to the list of existing Stack Operations.
     *
     * @since 1.1.0
     *
     * @param StackOperation $stackOperation
     *
     * @return self
     */
    public function addStackOperation(StackOperation $stackOperation)
    {
        $this->stackOperations[] = $stackOperation;

        return $this;
    }

    /**
     * @return array
     */
    public function getStackOptions()
    {
        return $this->stackOptions;
    }

    /**
     * @since 1.1.0
     *
     * @param array $options
     *
     * @return self
     */
    public function setStackOptions(array $options)
    {
        $this->stackOptions = $options;

        return $this;
    }

    /**
     * Sets a single Stack option to the list of existing Stack options.
     *
     * @since 1.1.0
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function addStackOption($key, $value)
    {
        $this->stackOptions[$key] = $value;

        return $this;
    }
}

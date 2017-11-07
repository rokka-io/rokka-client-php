<?php

namespace Rokka\Client\Core;

/**
 * Represents a collection of stack operations for an organization.
 */
class Stack
{
    /**
     * @var string|null Organization name
     */
    public $organization;

    /**
     * @var string Name of the stack
     */
    public $name;

    /**
     * @var \DateTime When this stack was first created
     */
    public $created;

    /**
     * @var StackOperation[] Collection of stack operations that this stack has
     */
    public $stackOperations;

    /**
     * @var array Array of stack options that this stack has
     */
    public $stackOptions;

    /**
     * @var array Array of stack expressions that this stack has
     */
    protected $stackExpressions = [];

    /**
     * Constructor.
     *
     * It's recommended to use one of the helper static methods to create this object instead of the constructor directly
     *
     * @see Stack::createFromConfig()
     *
     * @param string|null $organization    Organization name
     * @param string      $name            Stack name
     * @param array       $stackOperations Collection of stack operations
     * @param array       $stackOptions    Collection of stack options
     * @param \DateTime   $created         Created at
     */
    public function __construct($organization, $name, array $stackOperations = [], array $stackOptions = [], \DateTime $created = null)
    {
        $this->organization = $organization;
        $this->name = $name;
        $this->stackOperations = $stackOperations;
        $this->stackOptions = $stackOptions;
        $this->created = $created;
    }

    /**
     * Create a stack from the JSON data returned by the rokka.io API.
     *
     * @param string|array $data    JSON data
     * @param bool         $isArray If the data provided is already an array
     *
     * @return Stack
     */
    public static function createFromJsonResponse($data, $isArray = false)
    {
        if (!$isArray) {
            $data = json_decode($data, true);
        }

        $stack_operations = [];
        foreach ($data['stack_operations'] as $operation) {
            $stack_operations[] = StackOperation::createFromJsonResponse($operation, true);
        }

        $stack = new self(
            $data['organization'],
            $data['name'],
            $stack_operations,
            [],
            new \DateTime($data['created'])
        );

        if (isset($data['stack_options'])) {
            $stack->setStackOptions($data['stack_options']);
        }

        if (isset($data['stack_expressions'])) {
            $stack->setStackExpressions($data['stack_expressions']);
        }

        return $stack;
    }

    /**
     * Creates a Stack object from an array.
     *
     * $config = ['operations' => StackOperation[]
     *            'options' => $options,
     *            'expressions' => $expressions
     * ]
     *
     * All are optional, if operations doesn't exist, it will be a noop operation.
     *
     * @since 1.1.0
     *
     * @param $stackName
     * @param array $config
     * @param null  $organization
     *
     * @return Stack
     */
    public static function createFromConfig($stackName, array $config, $organization = null)
    {
        $stack = new self($organization, $stackName);

        if (isset($config['operations'])) {
            $stack->setStackOperations($config['operations']);
        }

        if (isset($config['options'])) {
            $stack->setStackOptions($config['options']);
        }

        if (isset($config['expressions'])) {
            $stack->setStackExpressions($config['expressions']);
        }

        return $stack;
    }

    /**
     * Get name of organization this stack belongs to.
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get name of stack for url.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get date of creation for this stack.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return StackOperation[]
     */
    public function getStackOperations()
    {
        return $this->stackOperations;
    }

    /**
     * @since 1.1.0
     *
     * @param StackOperation[] $operations
     *
     * @return $this
     */
    public function setStackOperations(array $operations)
    {
        $this->stackOperations = [];
        foreach ($operations as $operation) {
            $this->addOperation($operation);
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
     * @return $this
     */
    public function addOperation(StackOperation $stackOperation)
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
     * @return $this
     */
    public function setStackOptions(array $options)
    {
        $this->stackOptions = $options;

        return $this;
    }

    /**
     * @since 1.1.0
     *
     * @param array $stackExpressions
     *
     * @return $this
     */
    public function setStackExpressions(array $stackExpressions)
    {
        $this->stackExpressions = $stackExpressions;

        return $this;
    }

    /**
     * @since 1.1.0
     *
     * @return array
     */
    public function getStackExpressions(): array
    {
        return $this->stackExpressions;
    }

    /**
     * @since 1.1.0
     *
     * @param null|string $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Gets stack operations / options / expressions as one array.
     *
     * Useful for using this to sent as json to the Rokka API
     *
     * @since 1.1.0
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'operations' => $this->getStackOperations(),
            'options' => $this->getStackOptions(),
            'expressions' => $this->getStackExpressions(),
            ];
    }
}

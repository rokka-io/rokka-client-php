<?php

namespace Rokka\Client\Core;

use Rokka\Client\UriHelper;

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
     * @var string|null Name of the stack
     */
    public $name;

    /**
     * @var \DateTime|null When this stack was first created
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
     * @var StackExpression[]
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
     * @param string|null $name            Stack name
     * @param array       $stackOperations Collection of stack operations
     * @param array       $stackOptions    Collection of stack options
     * @param \DateTime   $created         Created at
     */
    public function __construct($organization = null, $name = null, array $stackOperations = [], array $stackOptions = [], \DateTime $created = null)
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
        if (isset($data['stack_operations']) && is_array($data['stack_operations'])) {
            foreach ($data['stack_operations'] as $operation) {
                $stack_operations[] = StackOperation::createFromJsonResponse($operation, true);
            }
        }

        $stack = new self(
            $data['organization'],
            $data['name'],
            $stack_operations,
            [],
            new \DateTime($data['created'])
        );

        if (isset($data['stack_options']) && is_array($data['stack_options'])) {
            $stack->setStackOptions($data['stack_options']);
        }

        if (isset($data['stack_expressions']) && is_array($data['stack_expressions'])) {
            $stack_expressions = [];
            foreach ($data['stack_expressions'] as $expression) {
                $stack_expressions[] = StackExpression::createFromJsonResponse($expression, true);
            }

            $stack->setStackExpressions($stack_expressions);
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
     * @param string      $stackName
     * @param array       $config
     * @param string|null $organization
     *
     * @return Stack
     */
    public static function createFromConfig(string $stackName, array $config, string $organization = null)
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
     * @return string|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @since 1.1.0
     *
     * @param null|string $organization
     *
     * @return self
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
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
     * Get date of creation for this stack.
     *
     * @return null|\DateTime
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
     * Returns all operations matching name
     *
     * @since 1.2.0
     * @param string $name operation name
     * @return StackOperation[]
     */
    public function getStackOperationsByName($name)
    {
        $stackOperations = [];
        foreach($this->stackOperations as $stackOperation) {
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

    /**
     * @since 1.1.0
     *
     * @param StackExpression[] $stackExpressions
     *
     * @return self
     */
    public function setStackExpressions(array $stackExpressions)
    {
        $this->stackExpressions = [];
        foreach ($stackExpressions as $stackExpression) {
            $this->addStackExpression($stackExpression);
        }

        return $this;
    }

    /**
     * Adds a Stack Expression to the list of existing Stack Expression.
     *
     * @since 1.1.0
     *
     * @param StackExpression $stackExpression
     *
     * @return self
     */
    public function addStackExpression(StackExpression $stackExpression)
    {
        $this->stackExpressions[] = $stackExpression;

        return $this;
    }

    /**
     * @since 1.1.0
     *
     * @return StackExpression[]
     */
    public function getStackExpressions()
    {
        return $this->stackExpressions;
    }

    /**
     * Gets stack operations / options / expressions as one array.
     * The values of the keys are objects for operations and expressions.
     *
     * Useful for using this to sent a stack as json to the Rokka API
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

    /**
     * Returns the stack url part as a dynamic stack for previewing.
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
     * Gets stack operations / options as "flat" array.
     *
     * Useful for generating dynamic stacks for example
     *
     * @since 1.2.0
     * @see UriHelper::getDynamicStackFromStackConfig()
     *
     * @return array
     */
    public function getConfigAsArray()
    {
        $config = ['operations' => []];
        foreach ($this->getStackOperations() as $operation) {
            $config['operations'][] =  $operation->toArray();
        }
        $config['options'] = $this->getStackOptions();

        return $config;
    }
}

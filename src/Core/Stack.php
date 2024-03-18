<?php

namespace Rokka\Client\Core;

/**
 * Represents a collection of stack operations for an organization.
 */
class Stack extends AbstractStack
{
    /**
     * @var string|null Organization name
     */
    public $organization;

    /**
     * @var StackExpression[]
     */
    protected $stackExpressions = [];

    /**
     * @var \DateTime|null When this stack was first created
     */
    private $created;

    /**
     * Constructor.
     *
     * You can also use the Stack::createFromConfig() helper static methods to create this object instead of the constructor directly
     *
     * @see Stack::createFromConfig()
     *
     * @param string|null $organization    Organization name
     * @param string|null $name            Stack name
     * @param array       $stackOperations Collection of stack operations
     * @param array       $stackOptions    Collection of stack options
     * @param \DateTime   $created         Created at
     */
    public function __construct($organization = null, $name = null, array $stackOperations = [], array $stackOptions = [], ?\DateTime $created = null)
    {
        parent::__construct($name, $stackOperations, $stackOptions);
        $this->organization = $organization;
        $this->created = $created;
    }

    /**
     * Create a stack from a decoded JSON data returned by the rokka.io API.
     *
     * @param array $data Decoded JSON data
     *
     * @return Stack
     */
    public static function createFromDecodedJsonResponse($data)
    {
        $stack_operations = [];
        if (isset($data['stack_operations']) && \is_array($data['stack_operations'])) {
            foreach ($data['stack_operations'] as $operation) {
                $stack_operations[] = StackOperation::createFromDecodedJsonResponse($operation);
            }
        }

        $stack = new self(
            $data['organization'],
            $data['name'],
            $stack_operations,
            [],
            new \DateTime($data['created'])
        );

        if (isset($data['stack_options']) && \is_array($data['stack_options'])) {
            $stack->setStackOptions($data['stack_options']);
        }

        if (isset($data['stack_variables']) && \is_array($data['stack_variables'])) {
            $stack->setStackVariables($data['stack_variables']);
        }

        if (isset($data['stack_expressions']) && \is_array($data['stack_expressions'])) {
            $stack_expressions = [];
            foreach ($data['stack_expressions'] as $expression) {
                $stack_expressions[] = StackExpression::createFromDecodedJsonResponse($expression);
            }

            $stack->setStackExpressions($stack_expressions);
        }

        return $stack;
    }

    /**
     * Create a stack from the JSON data returned by the rokka.io API.
     *
     * @param string $data JSON data
     *
     * @return Stack
     */
    public static function createFromJsonResponse($data)
    {
        return self::createFromDecodedJsonResponse(json_decode($data, true));
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
     * @param string|null $organization
     *
     * @return self
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
        if (isset($config['variables'])) {
            $stack->setStackVariables($config['variables']);
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
     * @param string|null $organization
     *
     * @return self
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get date of creation for this stack.
     *
     * @return \DateTime|null
     */
    public function getCreated()
    {
        return $this->created;
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
            'variables' => $this->getStackVariables(),
        ];
    }

    /**
     * Returns the stack url part as a dynamic stack for previewing.
     *
     * @since 1.2.0
     *
     * @throws \RuntimeException When the stack name could not be parsed correctly
     *
     * @return string
     */
    public function getDynamicUriString()
    {
        $stack = new StackUri('dynamic', $this->getStackOperations(), $this->getStackOptions(), $this->getStackVariables());

        return $stack->getStackUriString();
    }
}

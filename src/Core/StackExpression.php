<?php

namespace Rokka\Client\Core;

/**
 * @since 1.1.0
 */
class StackExpression
{
    /**
     * @var string
     */
    public $expression;

    /**
     * @var array
     */
    public $overrides;

    public function __construct($expression, $optionOverrides = [])
    {
        $this->expression = $expression;
        $this->overrides = ['options' => $optionOverrides];
    }

    /**
     * Create a stack from the decoded JSON data returned by the rokka.io API.
     *
     * @param array $data Decoded JSON data
     *
     * @return StackExpression
     */
    public static function createFromDecodedJsonResponse($data)
    {
        $expression = new self($data['expression']);
        $expression->overrides = $data['overrides'];

        return $expression;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     *
     * @return StackExpression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptionsOverrides()
    {
        return $this->overrides['options'];
    }

    /**
     * @param array $overrides
     *
     * @return self
     */
    public function setOptionsOverrides(array $overrides)
    {
        $this->overrides['options'] = $overrides;

        return $this;
    }

    /**
     * Adds a single option override to the options overrides.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function addOptionOverride($key, $value)
    {
        $this->overrides['options'][$key] = $value;

        return $this;
    }
}

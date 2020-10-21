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

    /**
     * @param string                               $expression
     * @param array<string, bool|float|int|string> $optionOverrides
     */
    public function __construct($expression, array $optionOverrides = [])
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
     * @return array<string, bool|float|int|string>
     */
    public function getOptionsOverrides()
    {
        return $this->overrides['options'];
    }

    /**
     * @param array<string, bool|float|int|string> $overrides
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
     * @param string                $key
     * @param bool|float|int|string $value
     *
     * @return self
     */
    public function addOptionOverride($key, $value)
    {
        $this->overrides['options'][$key] = $value;

        return $this;
    }

    /**
     * @return array<bool|float|int|string>
     */
    public function getVariablesOverrides()
    {
        return $this->overrides['variables'];
    }

    /**
     * @param array<string, bool|float|int|string> $overrides
     *
     * @return self
     */
    public function setVariablesOverrides(array $overrides)
    {
        $this->overrides['variables'] = $overrides;

        return $this;
    }

    /**
     * Adds a single variable override to the variables overrides.
     *
     * @param string                $key
     * @param bool|float|int|string $value
     *
     * @return self
     */
    public function addVariableOverride($key, $value)
    {
        $this->overrides['variables'][$key] = $value;

        return $this;
    }
}

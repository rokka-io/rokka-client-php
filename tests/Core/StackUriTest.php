<?php

namespace Core;

use Rokka\Client\Core\StackOperation;
use Rokka\Client\Core\StackUri;

class StackUriTest extends \PHPUnit\Framework\TestCase
{
    public function provide__construct()
    {
        return [
            'simple stack' => ['foo', 'foo', [], [], 'foo'],
            'simple stack with override' => ['foo/options-autoformat-true', 'foo', ['autoformat' => 'true'], [], 'foo/options-autoformat-true'],
            'simple stack with operations  override' => ['foo/resize-width-100', 'foo', [],  [new StackOperation('resize', ['width' => '100'])], 'foo/resize-width-100'],
            'simple stack with variables' => ['foo/resize-width-100/variables-w-100-h-200', 'foo', [],  [new StackOperation('resize', ['width' => '100'])], 'foo/resize-width-100--variables-h-200-w-100'],
            'simple stack with variables override' => ['foo/resize-width-100/variables-w-100-h-200/v-w-300', 'foo', [],  [new StackOperation('resize', ['width' => '100'])], 'foo/resize-width-100--variables-h-200-w-300'],
            'simple stack with expression' => ['foo/resize-width-[$w]/variables-w-100-h-200', 'foo', [],  [new StackOperation('resize', [], ['width' => '$w'])], 'foo/resize-width-%5B$w%5D--variables-h-200-w-100'],

            'dynamic with options' => ['dynamic/options-autoformat-true', 'dynamic', ['autoformat' => 'true'], [], 'dynamic/options-autoformat-true'],
            'dynamic with operations' => ['dynamic/resize-width-100--options-autoformat-true', 'dynamic', ['autoformat' => 'true'], [new StackOperation('resize', ['width' => '100'])], 'dynamic/resize-width-100--options-autoformat-true'],
            'dynamic with operations override' => ['dynamic/resize-width-100--options-autoformat-true/resize-width-200', 'dynamic', ['autoformat' => 'true'], [new StackOperation('resize', ['width' => '200'])], 'dynamic/resize-width-200--options-autoformat-true'],
            'dynamic with options override' => ['dynamic/resize-width-100--options-autoformat-true/options-autoformat-false', 'dynamic', ['autoformat' => 'false'], [new StackOperation('resize', ['width' => '100'])], 'dynamic/resize-width-100--options-autoformat-false'],
            'dynamic with 2 same operations' => ['dynamic/resize-width-100--resize-width-200', 'dynamic', [], [new StackOperation('resize', ['width' => '100']), new StackOperation('resize', ['width' => '200'])], 'dynamic/resize-width-100--resize-width-200'],
            'dynamic with 2 same operations and override' => ['dynamic/resize-width-100--resize-width-200/resize-width-300', 'dynamic', [], [new StackOperation('resize', ['width' => '300']), new StackOperation('resize', ['width' => '300'])], 'dynamic/resize-width-300--resize-width-300'],
        ];
    }

    /**
     * @dataProvider provide__construct
     *
     * @param mixed      $stackName
     * @param mixed      $name
     * @param mixed      $options
     * @param mixed      $operations
     * @param null|mixed $expectedUri
     */
    public function test__construct($stackName, $name, $options, $operations, $expectedUri = null)
    {
        $stack = new StackUri($stackName);
        $this->assertEquals($name, $stack->getName());
        $this->assertEquals($options, $stack->getStackOptions());
        $this->assertEquals($operations, $stack->getStackOperations());
        $this->assertEquals($expectedUri, $stack->getStackUriString());
    }
}

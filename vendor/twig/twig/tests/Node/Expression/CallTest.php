<?php

namespace Twig\Tests\Node\Expression;

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\Node\Expression\CallExpression;

class CallTest extends \PHPUnit\Framework\TestCase
{
    public function testGetArguments()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'date']);
        $this->assertEquals(['U', null], $node->getArguments('date', ['format' => 'U', 'timestamp' => null]));
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Positional arguments cannot be used after named arguments for function "date".
     */
    public function testGetArgumentsWhenPositionalArgumentsAfterNamedArguments()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'date']);
        $node->getArguments('date', ['timestamp' => 123456, 'Y-m-d']);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Argument "format" is defined twice for function "date".
     */
    public function testGetArgumentsWhenArgumentIsDefinedTwice()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'date']);
        $node->getArguments('date', ['Y-m-d', 'format' => 'U']);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Unknown argument "unknown" for function "date(format, timestamp)".
     */
    public function testGetArgumentsWithWrongNamedArgumentName()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'date']);
        $node->getArguments('date', ['Y-m-d', 'timestamp' => null, 'unknown' => '']);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Unknown arguments "unknown1", "unknown2" for function "date(format, timestamp)".
     */
    public function testGetArgumentsWithWrongNamedArgumentNames()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'date']);
        $node->getArguments('date', ['Y-m-d', 'timestamp' => null, 'unknown1' => '', 'unknown2' => '']);
    }

    /**
     * @expectedException        \Twig\Error\SyntaxError
     * @expectedExceptionMessage Argument "case_sensitivity" could not be assigned for function "substr_compare(main_str, str, offset, length, case_sensitivity)" because it is mapped to an internal PHP function which cannot determine default value for optional argument "length".
     */
    public function testResolveArgumentsWithMissingValueForOptionalArgument()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'substr_compare']);
        $node->getArguments('substr_compare', ['abcd', 'bc', 'offset' => 1, 'case_sensitivity' => true]);
    }

    public function testResolveArgumentsOnlyNecessaryArgumentsForCustomFunction()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'custom_function']);

        $this->assertEquals(['arg1'], $node->getArguments([$this, 'customFunction'], ['arg1' => 'arg1']));
    }

    public function testGetArgumentsForStaticMethod()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'custom_static_function']);
        $this->assertEquals(['arg1'], $node->getArguments(__CLASS__.'::customStaticFunction', ['arg1' => 'arg1']));
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage The last parameter of "Twig\Tests\Node\Expression\CallTest::customFunctionWithArbitraryArguments" for function "foo" must be an array with default value, eg. "array $arg = []".
     */
    public function testResolveArgumentsWithMissingParameterForArbitraryArguments()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'foo', 'is_variadic' => true]);
        $node->getArguments([$this, 'customFunctionWithArbitraryArguments'], []);
    }

    public static function customStaticFunction($arg1, $arg2 = 'default', $arg3 = [])
    {
    }

    public function customFunction($arg1, $arg2 = 'default', $arg3 = [])
    {
    }

    public function customFunctionWithArbitraryArguments()
    {
    }

    /**
     * @expectedException              \LogicException
     * @expectedExceptionMessageRegExp #^The last parameter of "Twig\\Tests\\Node\\Expression\\custom_Twig_Tests_Node_Expression_CallTest_function" for function "foo" must be an array with default value, eg\. "array \$arg \= \[\]"\.$#
     */
    public function testResolveArgumentsWithMissingParameterForArbitraryArgumentsOnFunction()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'foo', 'is_variadic' => true]);
        $node->getArguments('Twig\Tests\Node\Expression\custom_Twig_Tests_Node_Expression_CallTest_function', []);
    }

    /**
     * @expectedException              \LogicException
     * @expectedExceptionMessageRegExp #^The last parameter of "Twig\\Tests\\Node\\Expression\\CallableTestClass\:\:__invoke" for function "foo" must be an array with default value, eg\. "array \$arg \= \[\]"\.$#
     */
    public function testResolveArgumentsWithMissingParameterForArbitraryArgumentsOnObject()
    {
        $node = new Node_Expression_Call([], ['type' => 'function', 'name' => 'foo', 'is_variadic' => true]);
        $node->getArguments(new CallableTestClass(), []);
    }
}

class Node_Expression_Call extends CallExpression
{
    public function getArguments($callable, $arguments)
    {
        return parent::getArguments($callable, $arguments);
    }
}

class CallableTestClass
{
    public function __invoke($required)
    {
    }
}

function custom_Twig_Tests_Node_Expression_CallTest_function($required)
{
}

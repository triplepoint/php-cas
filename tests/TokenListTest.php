<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\TokenList;
use CAS\Token;

class TokenListTest extends TestCase
{
    /**
     * @dataProvider balancedParenthesesProvider
     */
    public function testbalancedParentheses($input)
    {
        $list = new Tokenlist($input);
        $list->testBalancedParentheses();
    }

    public function balancedParenthesesProvider()
    {
        return [
            [[
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
             ]],
            [[
                new Token\OpenParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\CloseParenthesis(),
             ]],
            [[
                new Token\OpenParenthesis(),
                new Token\OpenParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\CloseParenthesis(),
                new Token\CloseParenthesis(),
             ]],
        ];
    }

    /**
     * @dataProvider unbalancedParenthesesProvider
     * @expectedException \CAS\Exception\UnbalancedParentheses
     */
    public function testUnbalancedParentheses($input)
    {
        $list = new Tokenlist($input);
        $list->testBalancedParentheses();
    }

    public function unbalancedParenthesesProvider()
    {
        return [
            [[
                new Token\OpenParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
             ]],
             [[
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\CloseParenthesis(),
             ]],
             [[
                new Token\OpenParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\CloseParenthesis(),
                new Token\CloseParenthesis(),
             ]],
             [[
                new Token\OpenParenthesis(),
                new Token\OpenParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\CloseParenthesis(),
             ]],
             [[
                new Token\CloseParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\OpenParenthesis(),
             ]],
             [[
                new Token\CloseParenthesis(),
                new Token\CloseParenthesis(),
                new Token\Operand('x'),
                new Token\Operator('+', 1),
                new Token\Operand('1'),
                new Token\OpenParenthesis(),
                new Token\OpenParenthesis(),
             ]],
        ];
    }
}

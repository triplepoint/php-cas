<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Expression;
use CAS\Exception\UnbalancedParentheses;
use CAS\Exception\InvalidOperand;

class ExpressionTest extends TestCase
{
    /**
     * @dataProvider validRelationProvider
     */
    public function testExpressionToString($input, $output)
    {
        $exp = new Expression($input);
        $this->assertEquals($output, (string) $exp);
    }

    /**
     * @dataProvider unbalancedParenthesesProvider
     * @expectedException \CAS\Exception\UnbalancedParentheses
     */
    public function testUnbalancedParentheses($input)
    {
        $exp = new Expression($input);
    }

    /**
     * @dataProvider invalidOperandProvider
     * @expectedException \CAS\Exception\InvalidOperand
     */
    public function testInvalidOperands($input)
    {
        $exp = new Expression($input);
    }

    public function validRelationProvider()
    {
        return [
            ['1', '1'],
            ['12', '12'],
            ['12.345', '12.345'],
            ['x', 'x'],
            ['thingy', 'thingy'],
            ['    thingy     ', 'thingy'],
            ['thingy_thangy', 'thingy_thangy'],
            ['((((((x))))))', 'x'],
            ['1+2', '(1 + 2)'],
            ['1+2-3/4*5/6-7+8', '((((1 + 2) - (((3 / 4) * 5) / 6)) - 7) + 8)'],  // = 27/8
            ['a+b-c/d*e/f-g+h', '((((a + b) - (((c / d) * e) / f)) - g) + h)'],
            ['(((373.15 - x) * 3/2))', '(((373.15 - x) * 3) / 2)'],
        ];
    }

    public function unbalancedParenthesesProvider()
    {
        return [
            ['(x+1'],
            ['x+1)'],
            ['(x+1))'],
            ['((x+1)'],
            [')x+1('],
            ['))x+1(('],
        ];
    }

    public function invalidOperandProvider()
    {
        return [
            ['@'],
            ['&'],
            ['*'],
        ];
    }

}

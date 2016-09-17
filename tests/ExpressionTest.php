<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Expression;
use CAS\Exception\UnbalancedParentheses;
use CAS\Exception\InvalidOperand;

class ExpressionTest extends TestCase
{
    /**
     * @dataProvider validExpressionProvider
     */
    public function testExpressionToString($input, $output)
    {
        $exp = Expression::fromString($input);
        $this->assertEquals($output, (string) $exp);
    }

    /**
     * @dataProvider validExpressionPHPifiedProvider
     */
    public function testExpressionToPHPString($input, $output, $eval_output)
    {
        $exp = Expression::fromString($input);
        $string = $exp->toPhpString();
        $this->assertEquals($output, $string);

        // see the provider for the explanation here
        $thingy=$thingy_thangy=$M_NOT_A_CONSTANT=$PHP_VERSION=$a=$b=$c=$d=$e=$f=$g=$h=$x=2;

        eval("\$eval_result = {$string};\n");
        $this->assertEquals($eval_output, $eval_result);
    }

    /**
     * @dataProvider unbalancedParenthesesProvider
     * @expectedException \CAS\Exception\UnbalancedParentheses
     */
    public function testUnbalancedParentheses($input)
    {
        $exp = Expression::fromString($input);
    }

    /**
     * @dataProvider invalidOperandProvider
     * @expectedException \CAS\Exception\InvalidOperand
     */
    public function testInvalidOperands($input)
    {
        $exp = Expression::fromString($input);
    }

    public function validExpressionProvider()
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
            ['(x + y) + (x - y)', '(x + y) + (x - y)'],
            ['1+2', '(1 + 2)'],
            ['1+2-3/4*5/6-7+8', '((((1 + 2) - (((3 / 4) * 5) / 6)) - 7) + 8)'],  // = 27/8
            ['a+b-c/d*e/f-g+h', '((((a + b) - (((c / d) * e) / f)) - g) + h)'],
            ['(((373.15 - x) * 3/2))', '(((373.15 - x) * 3) / 2)'],
        ];
    }

    public function validExpressionPHPifiedProvider()
    {
        // Assume all the variables are 2, we'll handle setting that in the test above
        // $thingy=$thingy_thangy=$M_NOT_A_CONSTANT=$PHP_VERSION=$a=$b=$c=$d=$e=$f=$g=$h=$x=2;
        return [
            ['12.345', '12.345', 12.345],
            ['x', '$x', 2],
            ['thingy', '$thingy', 2],
            ['thingy_thangy', '$thingy_thangy', 2],
            ['M_PI', 'M_PI', M_PI],         // Is a real math constant, don't variable-ize it
            ['M_NOT_A_CONSTANT', '$M_NOT_A_CONSTANT', 2],   // Isn't a real constant
            ['PHP_VERSION', '$PHP_VERSION', 2],     // Isn't a M_ constant, not allowed
            ['a+b-c/d*e/f-g+h', '(((($a + $b) - ((($c / $d) * $e) / $f)) - $g) + $h)', 3],
            ['(((373.15 - x) * 3/2))', '(((373.15 - $x) * 3) / 2)', (((373.15-2)*3)/2)],
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

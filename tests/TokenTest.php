<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Token;
use CAS\Exception\Immutable;

class TokenTest extends TestCase
{
    public function testSettingProperties()
    {
        $token = new Token('test', Token::TYPE_OPERAND);

        $this->assertSame('test', $token->string);
        $this->assertSame(Token::TYPE_OPERAND, $token->type);
        $this->assertSame(null, $token->precedence);
    }

    public function testSettingPropertiesWithPrecedence()
    {
        $token = new Token('test', Token::TYPE_OPERAND, 2);

        $this->assertSame('test', $token->string);
        $this->assertSame(Token::TYPE_OPERAND, $token->type);
        $this->assertSame(2, $token->precedence);
    }

    /**
     * @dataProvider faultyParameterProvider
     * @expectedException \UnexpectedValueException
     */
    public function testFaultyConstructorValues($string, $type, $precedence)
    {
        $token = new Token($string, $type, $precedence);
    }

    public function testSetsExcept()
    {
        $token = new Token('test', Token::TYPE_OPERAND, 2);

        try {
            $token->string = 'newval';
            $this->fail();
        } catch ( Immutable $e) {}

        try {
            $token->type = 'newval';
            $this->fail();
        } catch ( Immutable $e) {}

        try {
            $token->precedence = 'newval';
            $this->fail();
        } catch ( Immutable $e) {}
    }

    public function faultyParameterProvider()
    {
        return [
            ['test_string', Token::TYPE_OPERAND, 'vibrophone'],
            ['test_string', 'vibrophone', null],
        ];
    }

}

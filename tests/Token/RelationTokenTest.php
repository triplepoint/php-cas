<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Token;
use CAS\Exception\Immutable;

class RelationTokenTest extends TestCase
{
    public function testSettingString()
    {
        $token = new Token\Relation('test');

        $this->assertSame('test', (string) $token);
    }

    public function testSetsThrowExceptions()
    {
        $token = new Token\Relation('test');

        try {
            $token->string = 'newval';
            $this->fail();
        } catch ( Immutable $e) {}
    }
}

<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Relation;
use CAS\Expression;
use CAS\Token;

class RelationTest extends TestCase
{
    /**
     * @dataProvider validRelationProvider
     */
    public function testRelationToString($lhs, $rel, $rhs, $string)
    {
        $relation = new Relation(
            Expression::fromString($lhs),
            $rel,
            Expression::fromString($rhs)
        );

        $this->assertEquals($string, (string) $relation);
    }

    /**
     * @dataProvider validRelationProvider
     */
    public function testfromString($lhs, $rel, $rhs, $string)
    {
        $relation = Relation::fromString($string);

        $this->assertEquals($string, (string) $relation);
    }

    public function validRelationProvider()
    {
        return [
            ['x',    new Token\Relation('='),  '12',   'x = 12'],
            ['1',    new Token\Relation('='),  '2',    '1 = 2'], // Even though it's not true, this relation can still be expressed.
            ['x+12', new Token\Relation('='),  'y-75', '(x + 12) = (y - 75)'],
            ['z',    new Token\Relation('<='), '2',    'z <= 2'],
            ['z',    new Token\Relation('>'), '2',    'z > 2'],
        ];
    }
}

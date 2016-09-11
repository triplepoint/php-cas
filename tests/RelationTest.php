<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\Relation;
use CAS\Expression;

class RelationTest extends TestCase
{
    /**
     * @dataProvider validRelationProvider
     */
    public function testRelationToString($lhs, $rel, $rhs, $string)
    {
        $relation = new Relation(
            new Expression($lhs),
            $rel,
            new Expression($rhs)
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
            ['x', RELATION::RELATION_EQUALS, '12', 'x = 12'],
            ['1', RELATION::RELATION_EQUALS, '2', '1 = 2'], // Even though it's not true, this relation can still be expressed.
            ['x+12', RELATION::RELATION_EQUALS, 'y-75', '(x + 12) = (y - 75)'],
            ['z', RELATION::RELATION_LESS_THAN_EQUALS, '2', 'z <= 2'],
        ];
    }
}

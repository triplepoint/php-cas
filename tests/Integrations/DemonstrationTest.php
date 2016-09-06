<?php
namespace CASTest\Integrations;

use PHPUnit\Framework\TestCase;
use CAS\RelationSolver;
use CAS\Relation;
use CAS\Expression;
use CAS\Exception\UnknownVariable;

class DemonstrationTest extends TestCase
{
    /**
     * @dataProvider validRelationProvider
     */
    public function testsolveForVariable($lhs, $rel, $rhs, $res_lhs, $res_rel, $res_rhs)
    {
        $solver = new RelationSolver(
            new Relation(
                new Expression($lhs),
                $rel,
                new Expression($rhs)
            )
        );

        $res_relation = $solver->solveFor($res_lhs);

        $this->assertInstanceOf(Relation::class, $res_relation);

        $this->assertEquals($res_lhs, (string) $res_relation->lhs);
        $this->assertEquals($res_rel, (string) $res_relation->relation);
        $this->assertEquals($res_rhs, (string) $res_relation->rhs);
    }

    /**
     * @expectedException \CAS\Exception\UnknownVariable
     */
    public function testsolveForUnknownVariableThrowsException()
    {
        $solver = new RelationSolver(
            new Relation(
                new Expression('(373.15 - x) * 3/2'),
                '=',
                new Expression('0')
            )
        );

        $x_exp = $solver->solveFor('y');
    }

    public function validRelationProvider()
    {
        return [
            ['x', '=', '2', 'x', '=', '2'],
            ['x', '=', '2 * y', 'x', '=', '2 * y'],
            ['x', '=', '2 * y', 'y', '=', 'x / 2'],
            ['x', '=', '2 + y', 'y', '=', '2 - x'],
            ['(373.15 - x) * 3/2', '=', '12', 'x', '=', '373.15 - ( 12 / 3 * 2 )'],
            ['(373.15 - x) * 3/2', '=', 'y',  'x', '=', '373.15 - ( y / 3 * 2 )'],
            ['(373.15 - x) * 3/2', '<', 'y',  'x', '>', '373.15 - ( 12 / 3 * 2 )'],
        ];
    }
}

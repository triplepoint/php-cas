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
    public function testsolveForVariable($relation_str, $solve_for, $solved_relation)
    {
        $relation = Relation::fromString($relation_str);

        $solver = new RelationSolver($relation);

        $res_relation = $solver->solveFor($solve_for);

        $this->assertEquals($solve_for, (string) $res_relation->lhs);

        $this->assertEquals($solved_relation, (string) $res_relation);
    }

    /**
     * @dataProvider invalidSolveForVariableProvider
     * @expectedException \CAS\Exception\UnknownVariable
     */
    public function testsolveForUnknownVariableThrowsException($relation, $solve_for)
    {
        $relation = Relation::fromString($relation);
        $solver = new RelationSolver($relation);
        $x_exp = $solver->solveFor($solve_for);
    }

    public function validRelationProvider()
    {
        return [
            ['x = 2',                   'x', 'x = 2'],
            ['2 = x',                   'x', 'x = 2'],
            ['x = 2 * y',               'x', 'x = (2 * y)'],
            ['2 * y = x',               'x', 'x = (2 * y)'],
            ['x = 2 * y',               'y', 'y = (x / 2)'],
            ['x = 2 + y',               'y', 'y = (x - 2)'],
            ['x = (2 * x) + y',         'y', 'y = (x - (2 * x))'],
            ['x = (2 * x) + y',         'x', 'x = (-1 * y)'],
            ['(373.15 - x) * 3/2 = 12', 'x', 'x = 373.15 - ((12 / 3) * 2)'],
            ['(373.15 - x) * 3/2 = y',  'x', 'x = 373.15 - ((y / 3) * 2)'],

            // I should check my logic on inequalities, I can't remember how the "or equals to" bits work
            // ['(373.15 - x) * 3/2 < y',  'x', 'x > 373.15 - ((12 / 3) * 2)'],
            // ['(373.15 - x) * 3/2 > y',  'x', 'x > 373.15 - ((12 / 3) * 2)'],
        ];
    }

    public function invalidSolveForVariableProvider()
    {
        // All of these should throw an unknown variable exception
        return [
            ['(373.15 - x) * 3/2 = 0', 'y'],
            ['(373.15 - x) * 3/2 = 0', '3'],
        ];
    }
}

<?php
namespace CASTest;

use PHPUnit\Framework\TestCase;
use CAS\RelationSolver;
use CAS\Relation;

class RelationSolverTest extends TestCase
{
    public function testRelationSolver()
    {
        $relation = Relation::fromString('x = 12');
        $solver = new RelationSolver($relation);

        $solver->solveFor('x');
    }
}

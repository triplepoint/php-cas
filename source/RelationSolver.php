<?php
namespace CAS;

class RelationSolver
{
    protected $relation;

    public function __construct(Relation $relation)
    {
        $this->relation = $relation;
    }

    public function solveFor($variable)
    {
        // Transform the relation into another relation, that solves for the
        // given variable

        $relation = new Relation( // TODO - stand-in value, for testing.
            new Expression($variable),
            '=',
            new Expression('something')
        );

        return $relation;
    }
}

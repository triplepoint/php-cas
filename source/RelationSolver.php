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
        return $this->relation; // TODO - remove this and do it right

        // Ensure that the variable is present in either the left or right hand
        // side expressions

        // Which-ever expression has the variable in it, move that to the left
        // hand side
        // ??? what if the solve variable is on both sides?

        // Shuffle elements off the top of the left hand side's tree onto the
        // right hand side, until the target variable's operator is at the top
        // of the left hand side.
        // ???? What if the target variable shows up in more than one level of the lhs?

        // Shuffle the operator and the other operand from the lhs to the rhs, leaving
        // the target variable isolated.
        // ??? Do we support -x ?
        // ??? what hapens if (12 - x) is the lhs?  We'll have to deal with negatives.


    }
}

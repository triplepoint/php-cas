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
        // Example starting relation:
        // ex: (((x + a) * b) + ((x + c) * d)) = (x + e)

        // 1 - Move all the terms to the left hand side
        // ex: ((((x + a) * b) + ((x + c) * d)) - (x + e)) = 0

        // 2 - While the left hand side term is not just the target variable, apply the algorithm:
        // - Factor the left hand side for the target variable, resulting in a set of polynomial terms
        // - Find any polynomial terms without the target variable on the LHS, and shift them to the RHS
        // - If there are no more additive terms to shift, divide both sides by the multiple of the target variable
        // example steps:
        // : (x * (b + d - 1)) + ba + dc = 0
        // : (x * (b + d - 1)) = 0 - ba - dc
        // : x = ((0 - ba - dc) / (b + d - 1))

        return $this->relation;
    }
}

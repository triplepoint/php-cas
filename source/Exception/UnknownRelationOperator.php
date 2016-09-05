<?php
namespace CAS\Exception;

class UnknownRelationOperator extends AbstractException
{
    protected $error = 'The given operator (:operator) is not a valid relation.';
}

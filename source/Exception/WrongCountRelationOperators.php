<?php
namespace CAS\Exception;

class WrongCountRelationOperators extends AbstractException
{
    protected $error = 'There can be 1 and only 1 relation operator in the relation string (:string).';
}

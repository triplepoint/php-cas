<?php
namespace CAS\Exception;

class MissingRelationOperator extends AbstractException
{
    protected $error = 'There was no relation operator in the given string (:string).';
}

<?php
namespace CAS\Exception;

class UnknownVariable extends AbstractException
{
    protected $error = 'The given variable (:variable) wasn\'t present in the equation (:equation).';
}

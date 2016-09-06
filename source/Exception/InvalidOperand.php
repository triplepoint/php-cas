<?php
namespace CAS\Exception;

class InvalidOperand extends AbstractException
{
    protected $error = 'The operand token (:operand) is not a valid string.';
}

<?php
namespace CAS\Exception;

class UnbalancedParentheses extends AbstractException
{
    protected $error = 'There are unbalanced parentheses in the expression ":expression".';
}

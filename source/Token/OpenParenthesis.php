<?php
namespace CAS\Token;

class OpenParenthesis extends AbstractToken
{
    public function __construct($string = '(')
    {
        $this->string = $string;
    }
}

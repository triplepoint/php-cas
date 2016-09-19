<?php
namespace CAS\Token;

class CloseParenthesis extends AbstractToken
{
    public function __construct($string = ')')
    {
        $this->string = $string;
    }
}

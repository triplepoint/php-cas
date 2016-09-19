<?php
namespace CAS\Token;

class Operator extends AbstractToken
{
    protected $precedence;

    public function __construct($string, $precedence)
    {
        parent::__construct($string);
        if (!is_integer($precedence)) {
            throw new \UnexpectedValueException("The precedence ($precedence) must be an integer.");
        }
        $this->precedence = $precedence;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, ['string', 'precedence'], true)) {
            throw new \UnexpectedValueException("Value ($name) not available.");
        }
        return $this->$name;
    }
}

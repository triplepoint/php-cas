<?php
namespace CAS\Token;
use CAS\Exception;

abstract class AbstractToken
{
    protected $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, ['string'], true)) {
            throw new \UnexpectedValueException("Value ($name) not available.");
        }
        return $this->$name;
    }

    /**
     * Changing state is not allowed on this object.
     */
    public function __set($name, $value)
    {
        throw new Exception\Immutable();
    }

    /**
     * A simple string representation of the token.
     */
    public function __toString()
    {
        return $this->string;
    }
}

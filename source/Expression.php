<?php
namespace CAS;

/**
 * An immutable representation of a mathematical expression, suitable for
 * representing one half of a mathematical relation.
 *
 * Note that this class implements a subset of mathemtical expressions called
 * arithmetic expressions.  Multiplication, division, addition, and subtraction
 * are represented, as is the factorial operator and parentheses.  But other
 * expressions like exponents and trigonometry functions, for example, are not
 * supported.
 */
class Expression
{
    public function __construct($expression)
    {
        // TODO - Parse the string expression into a tree structure
    }

    /**
     * return a string form of the expression.
     */
    public function __toString()
    {
        return ''; // TODO - needs implementing
    }

    /**
     * Return this expression as a valid string of PHP code.
     */
    public function toPhpString()
    {
        $string = (string) $this;
        // TODO - basically, put '$' in front of any term that isn't a number.
        // TODO - check first to see if a constant is_defined() for the variable first.
        return $string;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, [])) {
            throw new UnexpectedValueException("Value ($name) not available.");
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
}

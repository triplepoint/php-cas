<?php
namespace CAS;

class Token
{
    const TYPE_OPERAND = 0;
    const TYPE_OPERATOR = 1;
    const TYPE_OPEN_PARENTHESIS = 2;
    const TYPE_CLOSE_PARENTHESIS = 3;

    protected $types = [
        self::TYPE_OPERAND,
        self::TYPE_OPERATOR,
        self::TYPE_OPEN_PARENTHESIS,
        self::TYPE_CLOSE_PARENTHESIS,
    ];

    protected $string;
    protected $type;
    protected $precedence;

    public function __construct($string, $type, $precedence = null)
    {
        if (!in_array($type, $this->types)) {
            throw new \UnexpectedValueException("The type ($type) is not valid.");
        }

        $this->string = $string;
        $this->type = $type;
        $this->precedence = $precedence;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, ['string', 'type', 'precedence'])) {
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
}

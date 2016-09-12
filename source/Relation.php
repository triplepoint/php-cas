<?php
namespace CAS;

/**
 * An immutable representation of a mathematical binary relation
 * https://en.wikipedia.org/w/index.php?title=Binary_relation&oldid=721605643
 *
 * Specifically, objects of this class represent a pair of expressions, along
 * with a relationship operator that relates them.
 */
class Relation
{
    const RELATION_EQUALS = 0;
    const RELATION_LESS_THAN = 1;
    const RELATION_LESS_THAN_EQUALS = 2;
    const RELATION_GREATER_THAN = 3;
    const RELATION_GREATER_THAN_EQUALS = 4;

    // We'll put the "or equals to" symbols at the front of the list, to give
    // them a chance to be parsed before the '=' sign.  This lets us avoid
    // real tokenization and parsing.
    const RELATION_SYMBOLS = [
        Relation::RELATION_LESS_THAN_EQUALS     => '<=',
        Relation::RELATION_GREATER_THAN_EQUALS  => '>=',
        Relation::RELATION_EQUALS               => '=',
        Relation::RELATION_LESS_THAN            => '<',
        Relation::RELATION_GREATER_THAN         => '>',
    ];

    /**
     * Given a string representation of a relation (2 expressions separated by
     * a relation operator), build a new Relation and return it.
     * Throws an exception if no relation operator is found.
     */
    public static function fromString($string)
    {
        foreach (self::RELATION_SYMBOLS as $relation => $relation_symbol) {
            $symbol_pos = strpos($string, $relation_symbol);
            if ($symbol_pos !== false) {
                return new self(
                    Expression::fromString(substr($string, 0, $symbol_pos)),
                    $relation,
                    Expression::fromString(substr($string, $symbol_pos + strlen($relation_symbol)))
                );
            }
        }
        throw new Exception\MissingRelationOperator([':string' => $string]);
    }

    protected $lhs;
    protected $rhs;
    protected $relation;

    public function __construct(Expression $lhs, $relation, Expression $rhs)
    {
        if (!array_key_exists($relation, self::RELATION_SYMBOLS)) {
            throw new Exception\UnknownRelationOperator([':operator' => $relation]);
        }

        $this->lhs = $lhs;
        $this->relation = $relation;
        $this->rhs = $rhs;
    }

    public function __toString()
    {
        return (string) $this->lhs .
               ' '. self::RELATION_SYMBOLS[$this->relation] . ' ' .
               (string) $this->rhs;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, ['lhs', 'rhs', 'relation'], true)) {
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

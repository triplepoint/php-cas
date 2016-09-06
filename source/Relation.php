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

    protected $relation_symbols = [
        Relation::RELATION_EQUALS               => '=',
        Relation::RELATION_LESS_THAN            => '<',
        Relation::RELATION_LESS_THAN_EQUALS     => '<=',
        Relation::RELATION_GREATER_THAN         => '>',
        Relation::RELATION_GREATER_THAN_EQUALS  => '>=',
    ];

    protected $lhs;
    protected $rhs;
    protected $relation;

    public function __construct(Expression $lhs, $relation, Expression $rhs)
    {
        if (!in_array($relation, $this->relation_symbols)) {
            throw new Exception\UnknownRelationOperator([':operator' => $relation]);
        }

        $this->lhs = $lhs;
        $this->rhs = $rhs;
        $this->relation = $relation;
    }

    /**
     * return a string form of the whole relation.
     */
    public function __toString()
    {
        return (string) $this->lhs . $this->relation_symbols[$this->relation] . (string) $this->lhs;
    }

    /**
     * Return this whole relation as a valid string of PHP code.
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
        if (!in_array($name, ['lhs', 'rhs', 'relation'])) {
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

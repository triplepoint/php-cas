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
    /**
     * Given a string representation of a relation (2 expressions separated by
     * a relation operator), build a new Relation and return it.
     * Throws an exception if no relation operator is found.
     */
    public static function fromString($relation)
    {
        $split_tokens = [
            new Token\Relation('='),
            new Token\Relation('<'),
            new Token\Relation('<='),
            new Token\Relation('>'),
            new Token\Relation('>='),
        ];

        $token_list = TokenList::tokenizeString($relation, $split_tokens);

        if ($token_list->count() !== 3) {
            throw new Exception\WrongCountRelationOperators([':string' => $relation]);
        }

        return new self(
            Expression::fromString((string) $token_list[0]),
            $token_list[1],
            Expression::fromString((string) $token_list[2])
        );
    }

    protected $lhs;
    protected $rhs;
    protected $operator;

    public function __construct(Expression $lhs, $operator, Expression $rhs)
    {
        $this->lhs = $lhs;
        $this->operator = $operator;
        $this->rhs = $rhs;
    }

    public function __toString()
    {
        return (string) $this->lhs . ' ' .
               (string) $this->operator . ' ' .
               (string) $this->rhs;
    }

    /**
     * Only allow getting the specified properties.
     */
    public function __get($name)
    {
        if (!in_array($name, ['lhs', 'rhs', 'operator'], true)) {
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

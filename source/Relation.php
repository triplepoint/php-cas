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
        $recognized_tokens = [
            new Token('=', Token::TYPE_RELATION),
            new Token('<', Token::TYPE_RELATION),
            new Token('<=', Token::TYPE_RELATION),
            new Token('>', Token::TYPE_RELATION),
            new Token('>=', Token::TYPE_RELATION),
        ];

        $token_list = Tokenizer::tokenizeExpression($relation, $recognized_tokens);

        if (count($token_list) !== 3) {
            throw new Exception\WrongCountRelationOperators([':string' => $relation]);
        }

        return new self(
            Expression::fromString($token_list[0]->string),
            $token_list[1],
            Expression::fromString($token_list[2]->string)
        );
    }

    protected $lhs;
    protected $rhs;
    protected $relation;

    public function __construct(Expression $lhs, $relation, Expression $rhs)
    {
        $this->lhs = $lhs;
        $this->relation = $relation;
        $this->rhs = $rhs;
    }

    public function __toString()
    {
        return (string) $this->lhs .
               ' '. $this->relation->string . ' ' .
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

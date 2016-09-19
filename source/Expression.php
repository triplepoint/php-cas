<?php
namespace CAS;

/**
 * An immutable representation of a mathematical expression, suitable for
 * representing one half of a mathematical relation.
 *
 * Note that this class implements a subset of mathematical expressions called
 * arithmetic expressions.  Multiplication, division, addition, and subtraction
 * are represented, as are parentheses.  But other expressions like exponents
 * and trigonometry functions, for example, are not supported.
 */
class Expression
{
    /**
     * Given a string representation of an expression, return an Expression
     * object that represents it as a parsed data structure.
     */
    public static function fromString($expression)
    {
        $split_tokens = [
            new Token\OpenParenthesis(),
            new Token\CloseParenthesis(),
            new Token\Operator('*', 1),
            new Token\Operator('/', 1),
            new Token\Operator('+', 2),
            new Token\Operator('-', 2),
        ];

        $token_list = TokenList::tokenizeString($expression, $split_tokens);
        return new self($token_list);
    }

    /**
     * Find the lowest-precedence operator, picking the rightmost of duplicates,
     * and skipping over parenthesis blocks.  Return the index of the found
     * operator.  If there's no operator found, return false.
     */
    protected static function findSplitOperator(TokenList $token_list)
    {
        $operators = [];

        // Loop over all the tokens in the expression, last to first
        $token_list = $token_list->reverse(true);
        $paren_count = 0;
        foreach ($token_list as $position => $token) {

            // If this token is a parenthesis, handle the counter and move
            // on to the next token
            if ($token instanceof Token\CloseParenthesis) {
                ++$paren_count;
                continue;
            } elseif ($token instanceof Token\OpenParenthesis) {
                --$paren_count;
                continue;
            }

            // If we're inside a parenthesis pair, advance until we're out of it
            if ($paren_count !== 0) {
                continue;
            }

            // If the token is an operator, record its position in the operators
            // set, indexed by its precedence
            if ($token instanceof Token\Operator) {
                $operators[$token->precedence][] = $position;
            }
        }

        // If there were no operators in the expression, return false
        if (count($operators) === 0) {
            return false;
        }

        // With the set of identified operators organized by precedence and
        // position, return the index position of the right-most of the
        // least-precedence operators.
        ksort($operators);
        $least_precedence_operator_positions = array_pop($operators);

        sort($least_precedence_operator_positions);
        $rightmost_least_precedence_operator_position = array_pop($least_precedence_operator_positions);

        return $rightmost_least_precedence_operator_position;
    }

    /**
     * The normalized list of Tokens that are parsed into the expression.
     */
    protected $token_list;

    /**
     * can be either an array with 'lhs' and 'rhs' Expression elements, plus a
     * Token 'operator' element, or it can be a sole Token object representing
     * an operand.
     */
    protected $expression;

    /**
     * Given a tokenized expression as a list, parse it into a structured tree
     * where each element is either an operand Token, or another Expression.
     */
    public function __construct(TokenList $token_list)
    {
        $token_list->testBalancedParentheses();

        $this->token_list = $token_list->stripOuterParentheses();

        // If the token list is now empty, let's treat that as a single ""
        // operand token, for validation purposes.
        if ($this->token_list->count() === 0) {
            $this->token_list[] = new Token\Operand('');
        }

        $operator_index = self::findSplitOperator($this->token_list);

        if ($operator_index === false) {

            // If there were no operators identified in the token list, then it
            // must be a lone operand.
            $this->token_list[0]->validate();
            $this->expression = $this->token_list[0];

        } else {

            // Otherwise, split the token list on the identified operator Token,
            // into a left-hand side and a right-hand side Expression.
            $this->expression = [
                'lhs'      => new self($this->token_list->slice(0, $operator_index)),
                'operator' => $this->token_list[$operator_index],
                'rhs'      => new self($this->token_list->slice($operator_index + 1)),
            ];
        }
    }

    /**
     * Is this expression a single operand, or is it an infix operator and a pair
     * of operands?
     */
    public function isSingleOperand()
    {
        return is_subclass_of($this->expression, Token\AbstractToken::class);
    }

    public function expand()
    {
        // TODO
        return $this;
    }

    public function factorFor($variable)
    {
        // TODO
        // probably expand() to start with, then gather variables
        return $this;
    }

    public function __toString()
    {
        if ($this->isSingleOperand()) {
            return (string) $this->expression;
        }
        return  '(' .
            (string) $this->expression['lhs'] . ' ' .
            (string) $this->expression['operator'] . ' ' .
            (string) $this->expression['rhs'] .
            ')';
    }

    /**
     * Return this expression as a valid string of PHP code.
     * This just involves turning variables into $variables, while watching out
     * for PHP constants.
     */
    public function toPhpString()
    {
        if ($this->isSingleOperand()) {
            return $this->expression->toPhpString();
        }
        return  '(' .
            $this->expression['lhs']->toPhpString() . ' ' .
            (string) $this->expression['operator'] . ' ' .
            $this->expression['rhs']->toPhpString() .
            ')';
    }
}

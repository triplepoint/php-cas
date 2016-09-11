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
        $token_list = self::tokenizeExpression($expression);
        return new self($token_list);
    }

    /**
     * Given a string expression, break it up into an ordered list of the
     * various Token objects.
     */
    protected static function tokenizeExpression($expression)
    {
        $recognized_tokens = [
            new Token('(', Token::TYPE_OPEN_PARENTHESIS),
            new Token(')', Token::TYPE_CLOSE_PARENTHESIS),
            new Token('*', Token::TYPE_OPERATOR, 1),
            new Token('/', Token::TYPE_OPERATOR, 1),
            new Token('+', Token::TYPE_OPERATOR, 2),
            new Token('-', Token::TYPE_OPERATOR, 2),
        ];

        // Remove any spaces in the expression
        $expression = str_replace(' ', '', $expression);

        $tokens = [];
        $shift_buffer = '';

        // As long as there's still some expression string left to parse
        while (strlen($expression) > 0) {

            // Test each of the known tokens
            foreach ($recognized_tokens as $token) {

                // If this token is currently at the head of the expression
                if (strpos($expression, $token->string) === 0) {

                    // Save any buffered content as a new token
                    if ($shift_buffer !== '') {
                        $tokens[] = new Token($shift_buffer, Token::TYPE_OPERAND);
                        $shift_buffer = '';
                    }

                    // Add the identified token as a new token
                    $tokens[] = $token;

                    // Remove the identified token from the head of the
                    // expression string
                    $expression = substr($expression, strlen($token->string));

                    // Skip the rest of this foreach loop and also skip the rest
                    // of the while loop, and start over.
                    continue 2;
                }
            }

            // If no tokens were identified at the head of the expression,
            // shift another character off the expression string and into the
            // buffer and try again.
            $shift_buffer .= $expression[0];
            $expression = substr($expression, 1);
        }

        // Now that we're done parsing the expression string, if there's
        // anything still stored in the buffer, add it as a new token
        if ($shift_buffer !== '') {
            $tokens[] = new Token($shift_buffer, Token::TYPE_OPERAND);
            $shift_buffer = '';
        }

        // Return the parsed set of tokens
        return $tokens;
    }


    /**
     * Given a tokenized expression as a list, test whether the parentheses are
     * properly nested and balanced.
     */
    protected static function testBalancedParentheses(array $token_list)
    {
        // Step through the tokenized expression from left to right, looking
        // for unbalanced parentheses.
        $paren_count = 0;
        foreach ($token_list as $token) {

            // Keep track of the current open parenthesis count
            if ($token->type === TOKEN::TYPE_OPEN_PARENTHESIS) {
                ++$paren_count;
            } elseif ($token->type === TOKEN::TYPE_CLOSE_PARENTHESIS) {
                --$paren_count;
            }

            // If we've closed more than we've opened, that's an error
            if ($paren_count < 0) {
                throw new Exception\UnbalancedParentheses([':expression' => print_r($token_list, true)]);
            }
        }

        // Now that we're done, if there's still any unclosed parentheses,
        // that's also an error
        if ($paren_count !== 0) {
            throw new Exception\UnbalancedParentheses([':expression' => print_r($token_list, true)]);
        }
    }

    /**
     * Remove any and all matching pairs of parentheses that enclose the entire
     * expression.
     */
    protected static function stripOuterParentheses(array $token_list)
    {
        while (count($token_list) > 0 &&
               $token_list[0]->type === TOKEN::TYPE_OPEN_PARENTHESIS &&
               $token_list[count($token_list) - 1]->type === TOKEN::TYPE_CLOSE_PARENTHESIS
        ) {
            $token_list = array_slice($token_list, 1, -1);
        }
        return $token_list;
    }

    /**
     * Find the lowest-precedence operator, picking the rightmost of duplicates,
     * and skipping over parenthesis blocks.  Return the index of the found
     * operator.  If there's no operator found, return false.
     */
    protected static function findSplitOperator(array $token_list)
    {
        $operators = [];

        // Loop over all the tokens in the expression, last to first
        $token_list = array_reverse($token_list, true);
        $paren_count = 0;
        foreach ($token_list as $position => $token) {

            // If this token is a parenthesis, handle the counter and move
            // on to the next token
            if ($token->type === Token::TYPE_CLOSE_PARENTHESIS) {
                ++$paren_count;
                continue;
            } elseif ($token->type === Token::TYPE_OPEN_PARENTHESIS) {
                --$paren_count;
                continue;
            }

            // If we're inside a parenthesis pair, advance until we're out of it
            if ($paren_count !== 0) {
                continue;
            }

            // If the token is an operator, record its position in the operators
            // set, indexed by its precedence
            if ($token->type === Token::TYPE_OPERATOR) {
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
     * For operand Tokens, ensure that they're properly formed.  They can be:
     * - anything php considers is_numeric(), or
     * - anything that passes php's variable name rules
     * See php's documentation on variables:
     * http://php.net/manual/en/language.variables.basics.php
     */
    protected static function validateOperand(Token $operand)
    {
        if (is_numeric($operand->string) ||
            preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $operand->string) === 1
        ) {
            return;
        }

        throw new Exception\InvalidOperand([':operand' => $operand->string]);
    }

    /**
     * Given an operand token, convert it into valid PHP code snippet as a
     * string.  If the string is a defined constant that begins with 'M_',
     * we'll allow it on the assumption that it's one of PHP's builtin math
     * constants.
     * See http://php.net/manual/en/math.constants.php
     */
    protected static function operandAsPHP(Token $operand)
    {
        $string = $operand->string;
        if (is_numeric($string)) {
            return $string;
        } elseif (defined($string) && strpos($string, 'M_') === 0) {
            return $string;
        }
        return '$'.$string;
    }

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
    public function __construct(array $token_list)
    {
        self::testBalancedParentheses($token_list);

        $token_list = self::stripOuterParentheses($token_list);

        // If the token list is now empty, let's treat that as a single ""
        // operand token, for validation purposes.
        if (count($token_list) === 0) {
            $token_list[] = new Token('', Token::TYPE_OPERAND);
        }

        $operator_index = self::findSplitOperator($token_list);

        if ($operator_index === false) {

            // If there were no operators identified in the token list, then it
            // must be a lone operand.
            self::validateOperand($token_list[0]);
            $this->expression = $token_list[0];

        } else {

            // Otherwise, split the token list on the identified operator Token,
            // into a left-hand side and a right-hand side Expression.
            $this->expression = [
                'lhs'      => new self(array_slice($token_list, 0, $operator_index)),
                'operator' => $token_list[$operator_index],
                'rhs'      => new self(array_slice($token_list, $operator_index + 1)),
            ];
        }
    }

    /**
     * Is this expression a single operand, or is it an infix operator and a pair
     * of operands?
     */
    public function isSingleOperand()
    {
        return ($this->expression instanceof Token);
    }

    public function __toString()
    {
        if ($this->isSingleOperand()) {
            return $this->expression->string;
        }
        return  '(' .
            (string) $this->expression['lhs'] .
            ' ' . $this->expression['operator']->string . ' ' .
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
            return self::operandAsPHP($this->expression);
        }
        return  '(' .
            $this->expression['lhs']->toPhpString() .
            ' ' . $this->expression['operator']->string . ' ' .
            $this->expression['rhs']->toPhpString() .
            ')';
    }
}

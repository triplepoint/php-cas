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
    protected $defined_tokens;
    protected $token_list;
    protected $parse_tree;

    public function __construct($expression)
    {
        $this->defined_tokens = [
            new Token('(', Token::TYPE_OPEN_PARENTHESIS),
            new Token(')', Token::TYPE_CLOSE_PARENTHESIS),
            new Token('*', Token::TYPE_OPERATOR, 1),
            new Token('/', Token::TYPE_OPERATOR, 1),
            new Token('+', Token::TYPE_OPERATOR, 2),
            new Token('-', Token::TYPE_OPERATOR ,2),
        ];

        $this->token_list = $this->tokenizeExpression($expression);
        $this->parse_tree = $this->parseExpression($this->token_list);
    }

    /**
     * Given a string expression, break it up into an ordered list of the
     * various Token objects.
     */
    protected function tokenizeExpression($expression)
    {
        // Remove any spaces in the expression
        $expression = str_replace(' ', '', $expression);

        $tokens = [];
        $shift_buffer = '';

        // As long as there's still some expression string left to parse
        while (strlen($expression) > 0) {

            // Test each of the defined tokens
            foreach ($this->defined_tokens as $token) {

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

                    // Start over with what's left of the expression string
                    continue 2;
                }
            }

            // If no tokens were identified at the head of the expression,
            // shift another character into the buffer and try again.
            $shift_buffer .= $expression[0];
            $expression = substr($expression, 1);
        }

        // Now that we're done with the expression string, if there's anything
        // still stored in the buffer, write it as a new token
        if ($shift_buffer !== '') {
            $tokens[] = new Token($shift_buffer, Token::TYPE_OPERAND);
            $shift_buffer = '';
        }

        // Return the parsed set of tokens
        return $tokens;
    }

    /**
     * Given a tokenized expression as a list, parse it into a structured tree
     * where each element is either an operand Token, or an array with
     * recursive tree 'lhs' and 'rhs' elements plus an 'operator' Token.
     */
    protected function parseExpression(array $token_list)
    {
        $this->testBalancedParentheses($token_list);

        $token_list = $this->stripOuterParentheses($token_list);

        // If the token list is empty, let's treat that as a single "" token,
        // for parsing and validation purposes.
        if ($token_list === []) {
            $token_list = [
                new Token('', Token::TYPE_OPERAND)
            ];
        }

        $operator_index = $this->findNextOperator($token_list);

        if ($operator_index === false) {
            $this->testOperandForCorrectness($token_list[0]);
            return $token_list[0];
        }

        $operator = $token_list[$operator_index];
        $lhs = array_slice($token_list, 0, $operator_index);
        $rhs = array_slice($token_list, $operator_index + 1);
        return [
            'lhs'      => $this->parseExpression($lhs),
            'operator' => $operator,
            'rhs'      => $this->parseExpression($rhs),
        ];
    }

    /**
     * Given a tokenized expression as a list, test whether the parentheses are
     * properly nested and balanced.
     */
    protected function testBalancedParentheses(array $token_list)
    {
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

        // if there's still any unclosed parentheses, that's an error
        if ($paren_count !== 0) {
            throw new Exception\UnbalancedParentheses([':expression' => print_r($token_list, true)]);
        }
    }

    /**
     * Remove any and all matching pairs of parentheses that enclose the entire
     * expression.
     */
    protected function stripOuterParentheses(array $token_list)
    {
        while (count($token_list) >= 2 &&
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
    protected function findNextOperator(array $token_list)
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
        if ($operators === []) {
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
    protected function testOperandForCorrectness(Token $operand)
    {
        if (is_numeric($operand->string)) {
            return;
        } elseif (preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $operand->string) === 1) {
            return;
        }

        throw new Exception\InvalidOperand([':operand' => $operand->string]);
    }

    public function __toString()
    {
        return $this->getParseTreeAsString($this->parse_tree);
    }

    protected function getParseTreeAsString($tree)
    {
        // if the tree is a subtree
        if (array_key_exists('lhs', $tree)) {
            return '(' .
                $this->getParseTreeAsString($tree['lhs']) .
                ' ' . $tree['operator']->string . ' ' .
                $this->getParseTreeAsString($tree['rhs']) .
                ')';

        } elseif ($tree !== []) {
            // Otherwise if this is a operand Token, just return it as a string
            return $tree->string;

        } else {
            // Otherwise, it's an empty list, just return an empty string
            return '';
        }
    }

    /**
     * Return this expression as a valid string of PHP code.
     */
    // public function toPhpString()
    // {
    //     $string = (string) $this;
    //     // TODO - basically, put '$' in front of any term that isn't a number.
    //     // TODO - check first to see if a constant is_defined() for the variable first.
    //     return $string;
    // }
}

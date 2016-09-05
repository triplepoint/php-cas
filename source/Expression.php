<?php
namespace CAS;

/**
 * An immutable representation of a mathematical expression, suitable for
 * representing one half of a mathematical relation.
 *
 * Note that this class implements a subset of mathematical expressions called
 * arithmetic expressions.  Multiplication, division, addition, and subtraction
 * are represented, as is the factorial operator and parentheses.  But other
 * expressions like exponents and trigonometry functions, for example, are not
 * supported.
 */
class Expression
{
    const OPERATOR_MULTIPLY = 0;
    const OPERATOR_DIVIDE = 1;
    const OPERATOR_ADD = 2;
    const OPERATOR_SUBTRACT = 3;
    const OPERATOR_FACTORIAL = 4;

    protected $operator_symbols = [
        Expression::OPERATOR_MULTIPLY  => '*',
        Expression::OPERATOR_FACTORIAL => '!',
        Expression::OPERATOR_DIVIDE    => '/',
        Expression::OPERATOR_ADD       => '+',
        Expression::OPERATOR_SUBTRACT  => '-',
    ];

    protected $parse_tree;

    public function __construct($expression)
    {
        $token_list = $this->tokenizeExpression($expression);
        $this->parse_tree = $this->parseExpression($token_list);
    }

    /**
     * Given a string expression, break it up into a list of the various
     * tokens.  Each token is an array with these keys:
     * - string - the string value of the token
     */
    protected function tokenizeExpression($expression)
    {
        // Remove any spaces in the expression
        $expression = str_replace(' ', '', $expression);

        // These elements are considered distinct tokens on which to
        // break the expression.
        $defined_tokens = array_merge(
            ['(', ')'],
            array_values($this->operator_symbols)
        );

        // As long as there's still some expression string left to parse
        $tokens = [];
        $shift_buffer = '';
        while (strlen($expression) > 0) {

            // Test each of the known tokens
            foreach ($defined_tokens as $parse_token) {

                // If this token is currently at the head of the expression
                if (strpos($expression, $parse_token) === 0) {

                    // Save any buffered content as a new token
                    if ($shift_buffer !== '') {
                        $tokens[] = [
                            'string' => $shift_buffer,
                        ];
                        $shift_buffer = '';
                    }

                    // Bite off the token from the expression string
                    $expression = substr($expression, strlen($parse_token));

                    // Add the identified token as a new token
                    $tokens[] = [
                        'string' => $parse_token
                    ];

                    // Start over testing all the tokens
                    continue 2;
                }
            }

            // If no tokens were identified at the head of the expression,
            // shift off another character from the expression and try again.
            $shift_buffer .= $expression[0];
            $expression = substr($expression, 1);
        }

        // Now that we're done with the expression string, if there's anything
        // still stored in the buffer, write it as a new token
        if ($shift_buffer !== '') {
            $tokens[] = [
                'string' => $shift_buffer,
            ];
            $shift_buffer = '';
        }

        // Return the parsed set of tokens
        return $tokens;
    }

    /**
     * Given a tokenized expression, parse it into a structured tree where
     * each element is either a single string term, or an array with recursive
     * 'lhs' and 'rhs' elements plus an 'operator' element.
     */
    protected function parseExpression(array $token_list)
    {
        $this->testBalancedParentheses($token_list);

        $token_list = $this->stripOuterParentheses($token_list);

        $operator_index = $this->findNextLeastOperator($token_list);

        // If the expression was a single term (no operator found)
        if ($operator_index === false) {
            $this->testTermForCorrectness($token_list);
            return $token_list;
        }

        // Otherwise, split the expression on that operator,
        // and recurse both sides
        $operator = $token_list[$operator_index];
        $lhs = array_slice($token_list, 0, $operator_position);
        $rhs = array_slice($token_list, $operator_position + 1);
        return [
            'lhs'      => $this->parseExpression($lhs),
            'operator' => $operator,
            'rhs'      => $this->parseExpression($rhs),
        ];
    }

    /**
     * Given a tokenized expression, ensure that parentheses are properly
     * nested and balanced.
     */
    protected function testBalancedParentheses(array $token_list)
    {
        $paren_count = 0;
        foreach ($token_list as $token) {

            // Keep track of the current parenthesis count
            if ($token['string'] === '(') {
                ++$paren_count;
            } elseif ($token['string'] === ')') {
                --$paren_count;
            }

            // If we've closed more than we've opened, that's an error
            if ($paren_count < 0) {
                throw new Exception\UnbalancedParentheses([':expression' => print_r($expression, true)]);
            }
        }

        // if there's still any unclosed parentheses, that's an error
        if ($paren_count !== 0) {
            throw new Exception\UnbalancedParentheses([':expression' => print_r($expression, true)]);
        }
    }

    /**
     * Remove any and all matching pairs of parentheses that enclose the entire
     * expression.
     */
    protected function stripOuterParentheses(array $token_list)
    {
        while (count($token_list) >= 2 &&
               $token_list[0]['string'] === '(' &&
               $token_list[count($token_list) - 1]['string'] === ')'
        ) {
            $token_list = array_slice($token_list, 1, -1);
        }
        return $token_list;
    }

    /**
     * Find the lowest-ranked operator, picking the rightmost of duplicates,
     * and skipping over parenthesis blocks.  Return the index of the found
     * operator.  If there's no operator found, return false.
     */
    protected function findNextLeastOperator(array $token_list)
    {
        $gathered_operators = [];

        // Loop over all the characters in the expression, last to first
        $token_list = array_reverse($token_list, true);
        $paren_count = 0;
        foreach ($token_list as $index => $token) {

            // Keep track of the current parenthesis count
            if ($token['string'] === ')') {
                ++$paren_count;
            } elseif ($token['string'] === '(') {
                --$paren_count;
            }

            // If we're in a parenthesis, advance until we're out of it
            if ($paren_count !== 0) {
                continue;
            }

            // If the character is in the operator set, log it in the
            // gathered operators set
            // TODO finish this
            // if (in_array($character, haystack)) {
            //     $gathered_operators['priority'][] = thing;
            // }
        }

        // With the set of identified operators organized by priority, return
        // the index position of the right-most of the least-ranked operator.

        return false;
    }

    protected function testTermForCorrectness($term)
    {
        // TODO - not clear what a valid term check would be
        // Throw an exception if it doesn't validate
        // examples of invalid terms that might show up
        // - '2x'
        // Rather than blacklist bad forms, might just be best to whitelist the
        // allowed term types
        // - '1'
        // - '1.234'
        // - 'x'
        // - 'M_PI'
        // - 'pi'
        // How about:
        // - anything that passes is_numeric() is ok - it's a number
        // - anything that matches /[A-Za-z]+([_]+[A-Za-z]+)*/  - letters with underscores enclosed
    }

    /**
     * return a string form of the expression.
     */
    public function __toString()
    {
        return ''; // TODO - needs implementing - crawl upward through the
        // tree and assemble with explicit () pairs
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

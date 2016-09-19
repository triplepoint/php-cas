<?php
namespace CAS;

/**
 * Contains an ordered list of Tokens, representing a tokenized string.
 */
class TokenList extends \ArrayObject
{
    /**
     * Given a string, and a list of recognized tokens, break
     * up the string into an ordered list of the various Token objects.
     *
     * @param  string  $string
     * @param  AbstractToken[] $split_tokens An array of Token objects, representing
     *                                       unique tokens on which to break the string.
     * @return TokenList             An array of Token objects
     */
    public static function tokenizeString($string, array $split_tokens)
    {
        // Sort the tokens so that tokens with longer strings get a
        // chance to match before shorter ones
        usort($split_tokens, function ($a, $b) {
            $sa = (string) $a;
            $sb = (string) $b;
            if (strlen($sa) == strlen($sb)) {
                return 0;
            }
            return (strlen($sa) > strlen($sb)) ? -1 : 1;
        });

        // Remove any spaces in the string
        $string = str_replace(' ', '', $string);

        $tokens = new self();
        $shift_buffer = '';

        // As long as there's still some string left to parse
        while (strlen($string) > 0) {

            // Test each of the known tokens
            foreach ($split_tokens as $token) {

                // If this token is currently at the head of the string
                if (strpos($string, (string) $token) === 0) {

                    // Save any buffered content as a new token
                    if ($shift_buffer !== '') {
                        $tokens[] = new Token\Operand($shift_buffer);
                        $shift_buffer = '';
                    }

                    // Add the identified token as a new token
                    $tokens[] = $token;

                    // Remove the identified token from the head of the
                    // string
                    $string = substr($string, strlen((string) $token));

                    // Skip the rest of this foreach loop and also skip the rest
                    // of the while loop, and start over.
                    continue 2;
                }
            }

            // If no tokens were identified at the head of the string,
            // shift another character off the string and into the
            // buffer and try again.
            $shift_buffer .= $string[0];
            $string = substr($string, 1);
        }

        // Now that we're done parsing the string, if there's
        // anything still stored in the buffer, add it as a new token
        if ($shift_buffer !== '') {
            $tokens[] = new Token\Operand($shift_buffer);
            $shift_buffer = '';
        }

        // Return the parsed set of tokens
        return $tokens;
    }

    /**
     * Generate a plausible string representation of the token list.
     */
    public function __toString()
    {
        $strings = [];
        foreach ($this as $token) {
            $strings[] = (string) $token;
        }
        return implode(' ', $strings);
    }

    /**
     * Add methods mirroring a few array_* PHP functions, since the php
     * array functions themselves don't accept ArrayObject parameters.
     * http://php.net/manual/en/class.arrayobject.php#107079
     */
    public function __call($func, $argv)
    {
        $allowed_functions = [
            'reverse' => 'array_reverse',
            'slice'   => 'array_slice',
        ];
        if (!array_key_exists($func, $allowed_functions) || !is_callable($allowed_functions[$func])) {
            throw new \BadMethodCallException(__CLASS__.'->'.$func);
        }
        return new self(call_user_func_array($allowed_functions[$func], array_merge([$this->getArrayCopy()], $argv)));
    }

    /**
     * Test whether the list's parentheses Tokens are properly nested and balanced.
     */
    public function testBalancedParentheses()
    {
        // Step through the tokenized expression from left to right, looking
        // for unbalanced parentheses.
        $paren_count = 0;
        foreach ($this as $token) {

            // Keep track of the current open parenthesis count
            if ($token instanceof Token\OpenParenthesis) {
                ++$paren_count;
            } elseif ($token instanceof Token\CloseParenthesis) {
                --$paren_count;
            }

            // If we've closed more than we've opened, that's an error
            if ($paren_count < 0) {
                throw new Exception\UnbalancedParentheses([':expression' => (string) $this]);
            }
        }

        // Now that we're done, if there's still any unclosed parentheses,
        // that's also an error
        if ($paren_count !== 0) {
            throw new Exception\UnbalancedParentheses([':expression' => (string) $this]);
        }
    }

    /**
     * Remove any and all matching pairs of parentheses Tokens that enclose the entire
     * list.
     */
    public function stripOuterParentheses()
    {
        $token_list = clone($this);
        while ($token_list->count() > 0 &&
               $token_list[0] instanceof Token\OpenParenthesis &&
               $token_list[$token_list->count() - 1] instanceof Token\CloseParenthesis
        ) {
            try {
                $new_token_list = $token_list->slice(1, -1);
                $new_token_list->testBalancedParentheses();
                $token_list = $new_token_list;
            } catch (Exception\UnbalancedParentheses $e) {
                return $token_list;
            }
        }
        return $token_list;
    }
}

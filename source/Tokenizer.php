<?php
namespace CAS;

class Tokenizer
{
    /**
     * Given a string expression, and a list of recognized tokens, break
     * up the string into an ordered list of the various Token objects.
     *
     * @param  string $expression
     * @param  array  $recognized_tokens An array of Token objects, representing
     *                                   unique tokens on which to break the string.
     * @return array                     An array of Token objects
     */
    public static function tokenizeExpression($expression, array $recognized_tokens)
    {
        // Sort the tokens so that longer tokens get a chance to match before
        // shorter ones
        usort($recognized_tokens, function ($a, $b) {
            if (strlen($a->string) == strlen($b->string)) {
                return 0;
            }
            return (strlen($a->string) > strlen($b->string)) ? -1 : 1;
        });

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
}

<?php
namespace CAS\Token;
use CAS\Exception;

class Operand extends AbstractToken
{
    /**
     * Ensure the operand string is properly formed.  It can be:
     * - anything php considers is_numeric(), or
     * - anything that passes php's variable name rules
     * See php's documentation on variables:
     * http://php.net/manual/en/language.variables.basics.php
     */
    public function validate()
    {
        if (is_numeric($this->string) ||
            preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $this->string) === 1
        ) {
            return;
        }

        throw new Exception\InvalidOperand([':operand' => $this->string]);
    }

    /**
     * Convert the operand into valid PHP code snippet as a string.
     * If the string is a defined constant that begins with 'M_',
     * we'll allow it on the assumption that it's one of PHP's builtin math
     * constants.
     * See http://php.net/manual/en/math.constants.php
     */
    public function toPhpString()
    {
        $string = $this->string;
        if (is_numeric($string)) {
            return $string;
        } elseif (defined($string) && strpos($string, 'M_') === 0) {
            return $string;
        }
        return '$'.$string;
    }
}



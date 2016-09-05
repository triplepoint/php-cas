<?php
namespace CAS\Exception;

use Exception;

abstract class AbstractException extends Exception
{
    /**
     * The error message template for this exception.
     * Override this to generate a concrete exception.
     *
     * @var string
     */
    protected $error = '';

    /**
     * given an array of replacement values, assemble the error message for
     * this exception.
     *
     * @param array $parameters key/value replacement pairs for the error message.
     */
    public function __construct(array $parameters = [])
    {
        $message = strtr($this->error, $parameters);
        parent::__construct($message);
    }
}

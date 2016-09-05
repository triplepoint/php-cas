<?php
namespace CAS\Exception;

class Immutable extends AbstractException
{
    protected $error = 'Setting values is not allowed on this object..';
}

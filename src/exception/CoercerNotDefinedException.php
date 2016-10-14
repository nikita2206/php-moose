<?php

namespace moose\exception;

use Exception;
use moose\MooseException;

class CoercerNotDefinedException extends \RuntimeException
    implements MooseException
{
    public $type;

    public function __construct(string $type, Exception $previous = null)
    {
        parent::__construct("Coercer for type {$type} is not defined", 0, $previous);

        $this->type = $type;
    }
}

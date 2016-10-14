<?php

namespace moose\annotation\exception;

use moose\MooseException;

class InvalidTypeException extends \RuntimeException
    implements MooseException
{
    public $annotation;
    public $field;
    public $expected;
    public $actual;

    public function __construct(string $annotation, string $field, string $expected, string $actual)
    {
        parent::__construct("Annotation field {$annotation}::{$field} got {$actual}, was expecting {$expected}");

        $this->annotation = $annotation;
        $this->field = $field;
        $this->expected = $expected;
        $this->actual = $actual;
    }
}

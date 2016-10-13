<?php

namespace Moose\Error;

class CoercingError extends Error
{
    private $expected;
    private $value;

    public function __construct(string $expected, $value, $field = null)
    {
        parent::__construct($field);

        $this->expected = $expected;
        $this->value = $value;
    }

    public function getExpected(): string
    {
        return $this->expected;
    }

    public function getValue()
    {
        return $this->value;
    }
}

<?php

namespace Moose\Error;

class TypeError extends Error
{
    private $expected;
    private $actual;

    public function __construct(string $expected, string $actual, $field = null)
    {
        parent::__construct($field);

        $this->expected = $expected;
        $this->actual = $actual;
    }

    public function getExpected(): string
    {
        return $this->expected;
    }

    public function getActual(): string
    {
        return $this->actual;
    }
}

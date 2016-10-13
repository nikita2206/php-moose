<?php

namespace Moose\Error;

class InvalidDateFormatError extends Error
{
    private $expectedFormat;
    private $value;

    public function __construct(string $expectedFormat, $value, $field = null)
    {
        parent::__construct($field);

        $this->expectedFormat = $expectedFormat;
        $this->value = $value;
    }

    public function getExpectedFormat(): string
    {
        return $this->expectedFormat;
    }

    public function getValue()
    {
        return $this->value;
    }
}

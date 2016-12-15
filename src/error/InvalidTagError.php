<?php

namespace moose\error;

class InvalidTagError extends Error
{
    private $tag;
    private $expected;

    public function __construct(string $tag, array $expected, string $field = null)
    {
        parent::__construct($field);

        $this->tag = $tag;
        $this->expected = $expected;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getExpected(): array
    {
        return $this->expected;
    }
}

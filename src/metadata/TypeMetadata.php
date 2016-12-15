<?php

namespace moose\metadata;

class TypeMetadata
{
    public $type;
    public $args;

    public function __construct(string $type = null, array $args = null)
    {
        $this->type = $type;
        $this->args = $args;
    }
}

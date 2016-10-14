<?php

namespace moose\annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class IntField extends Field
{
    public function getTypeName(): string
    {
        return "int";
    }
}

<?php

namespace Moose\Annotation;

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

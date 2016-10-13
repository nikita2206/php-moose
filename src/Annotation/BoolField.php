<?php

namespace Moose\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class BoolField extends Field
{
    public function getTypeName(): string
    {
        return "bool";
    }
}

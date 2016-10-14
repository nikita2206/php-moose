<?php

namespace moose\annotation;

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

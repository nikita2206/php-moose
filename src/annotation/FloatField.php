<?php

namespace moose\annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class FloatField extends Field
{
    public function getTypeName(): string
    {
        return "float";
    }
}

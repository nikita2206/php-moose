<?php

namespace Moose\Annotation;

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

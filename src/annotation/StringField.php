<?php

namespace moose\annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class StringField extends Field
{
    public function getTypeName(): string
    {
        return "string";
    }
}

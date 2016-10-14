<?php

namespace moose\metadata;

class FieldMetadata
{
    /**
     * @var TypeMetadata
     */
    public $type;
    public $classname;
    public $optional;

    /**
     * Field name in the $classname
     *
     * @var string
     */
    public $field;

    /**
     * Field name in the original payload.
     *
     * @var string
     */
    public $origin;
}

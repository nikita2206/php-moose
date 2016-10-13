<?php

namespace Moose\Annotation;
use Moose\Annotation\Exception\InvalidTypeException;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class DateField extends Field
{
    public $format;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $options["format"] = $options["value"];
        }
        if ( ! isset($options["format"])) {
            throw new InvalidTypeException(DateField::class, "format", "string", "null");
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return [$this->format];
    }

    public function getTypeName(): string
    {
        return "date";
    }
}

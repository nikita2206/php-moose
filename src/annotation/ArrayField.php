<?php

namespace moose\annotation;

use moose\annotation\exception\InvalidTypeException;
use function moose\type;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ArrayField extends Field
{
    /**
     * @var Field
     */
    public $T;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $options["T"] = $options["value"];
        }
        if (isset($options["T"]) && ! $options["T"] instanceof Field) {
            throw new InvalidTypeException(self::class, "T", Field::class, type($options["T"]));
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return $this->T ? [$this->T] : null;
    }

    public function getTypeName(): string
    {
        return "array";
    }
}

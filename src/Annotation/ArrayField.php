<?php

namespace Moose\Annotation;
use Moose\Annotation\Exception\InvalidTypeException;

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
            throw new InvalidTypeException(self::class, "T", Field::class, \gettype($options["T"]));
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return [$this->T];
    }

    public function getTypeName(): string
    {
        return "array";
    }
}

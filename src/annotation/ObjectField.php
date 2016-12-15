<?php

namespace moose\annotation;

use moose\annotation\exception\InvalidTypeException;
use function moose\type;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ObjectField extends Field
{
    /**
     * @var string
     */
    public $classname;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $options["classname"] = $options["value"];
        }
        if ( ! isset($options["classname"]) || ! class_exists($options["classname"])) {
            throw new InvalidTypeException(self::class, "classname", "classname", type($options["classname"] ?? null));
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return [$this->classname];
    }

    public function getTypeName(): string
    {
        return "object";
    }
}

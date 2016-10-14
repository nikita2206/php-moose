<?php

namespace moose\annotation;
use moose\annotation\exception\InvalidTypeException;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class MapField extends Field
{
    /**
     * @var Field
     */
    public $V;

    /**
     * @var Field
     */
    public $K;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $options["V"] = $options["value"];
        }
        if (isset($options["K"]) && ! $options["K"] instanceof Field) {
            throw new InvalidTypeException(self::class, "K", Field::class, \gettype($options["K"]));
        }
        if (isset($options["V"]) && ! $options["V"] instanceof Field) {
            throw new InvalidTypeException(self::class, "V", Field::class, \gettype($options["V"]));
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return $this->K ? [$this->K, $this->V] : [$this->V];
    }

    public function getTypeName(): string
    {
        return "map";
    }
}

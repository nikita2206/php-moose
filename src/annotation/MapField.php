<?php

namespace moose\annotation;

use moose\annotation\exception\InvalidTypeException;
use function moose\type;

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
            throw new InvalidTypeException(self::class, "K", Field::class, type($options["K"]));
        }
        if (isset($options["V"]) && ! $options["V"] instanceof Field) {
            throw new InvalidTypeException(self::class, "V", Field::class, type($options["V"]));
        }
        if (isset($options["K"]) && ! isset($options["V"])) {
            throw new InvalidTypeException(self::class, "V", Field::class, "none");
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return $this->K ? [$this->K, $this->V] : ($this->V ? [$this->V] : null);
    }

    public function getTypeName(): string
    {
        return "map";
    }
}

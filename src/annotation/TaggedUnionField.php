<?php

namespace moose\annotation;

use moose\annotation\exception\InvalidTypeException;
use function moose\type;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class TaggedUnionField extends Field
{
    /**
     * @var string[]
     */
    public $map;

    /**
     * @var string
     */
    public $tagField;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $tagField = $options["tag"] = $options["value"];
        } else {
            $tagField = $options["tag"] ?? null;
        }

        if ($tagField === null || ! \is_string($tagField)) {
            throw new InvalidTypeException(self::class, "tag", "string", type($tagField));
        }

        $map = $options["map"] ?? null;
        if ($map  === null || ! \is_array($options["map"])) {
            throw new InvalidTypeException(self::class, "map", "a map of tag => classname", type($map));
        }

        foreach ($map as $tag => $classname) {
            if ( ! $classname instanceof Field && ( ! \is_string($classname) || ! \class_exists($classname))) {
                throw new InvalidTypeException(self::class, "map[{$tag}]", "a classname", type($classname) . " (class doesn't exist)");
            }
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return [$this->tagField, $this->map];
    }

    public function getTypeName(): string
    {
        return "tagged_union";
    }
}

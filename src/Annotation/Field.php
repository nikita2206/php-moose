<?php

namespace Moose\Annotation;

/**
 * You need to extend this annotation class in order to add custom types to Moose
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
abstract class Field
{
    /**
     * @var string
     */
    public $origin;

    /**
     * @var bool
     */
    public $optional;

    public function __construct(array $options)
    {
        foreach ($options as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * @return null|array
     */
    public function getArgs()
    {
        return null;
    }

    abstract public function getTypeName(): string;
}

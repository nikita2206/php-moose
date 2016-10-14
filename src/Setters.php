<?php

namespace moose;

class Setters
{
    private $cache;
    private $prototype;

    public function __construct()
    {
        $this->cache = [];
        $this->prototype = static function ($instance, $field, $value) {
            $instance->$field = $value;
        };
    }

    public function setter($classname): \Closure
    {
        if (isset($this->cache[$classname])) {
            return $this->cache[$classname];
        }

        return $this->cache[$classname] = $this->prototype->bindTo(null, $classname);
    }
}

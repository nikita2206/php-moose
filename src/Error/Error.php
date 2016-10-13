<?php
declare(strict_types=1);

namespace Moose\Error;

abstract class Error
{
    /**
     * @var string
     */
    private $field;

    public function __construct(string $field = null)
    {
        $this->field = $field;
    }

    public function inField(string $field)
    {
        $new = clone $this;
        $new->field = $this->joinFields($field, $this->field);

        return $new;
    }

    public function atIndex($idx)
    {
        $new = clone $this;
        $new->field = $this->joinFields("[{$idx}]", $this->field);

        return $new;
    }

    public function getField()
    {
        return $this->field;
    }

    private function joinFields(string $l, string $r = null): string
    {
        if ($r === null) {
            return $l;
        }
        if ($r[0] === "[") {
            return $l . $r;
        }

        return "{$l}.{$r}";
    }
}

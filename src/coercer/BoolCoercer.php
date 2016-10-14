<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;

class BoolCoercer implements TypeCoercer
{
    private $false = ["no", "false", "N", "F"];
    private $true = ["yes", "true", "Y", "T"];

    public function __construct($true = null, $false = null)
    {
        $this->true = $true ?? $this->true;
        $this->false = $false ?? $this->false;
    }

    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_bool($value) && ! \is_int($value) && ! \is_string($value) && ! \is_float($value)) {
            return ConversionResult::error(new TypeError("bool", \gettype($value)));
        }

        if (\is_string($value)) {
            $value = trim($value);

            if (\is_numeric($value)) {
                $value = (bool)(int)$value;
            } elseif (\in_array($value, $this->false, true)) {
                $value = false;
            } elseif (\in_array($value, $this->true, true)) {
                $value = true;
            } else {
                $value = (bool)$value;
            }
        } else {
            $value = (bool)$value;
        }

        return ConversionResult::value($value);
    }
}

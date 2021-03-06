<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class StringCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_string($value) && ! \is_int($value) && ! \is_float($value)) {
            return ConversionResult::error(new TypeError("string", type($value)));
        }

        return ConversionResult::value((string)$value);
    }
}

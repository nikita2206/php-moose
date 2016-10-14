<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class IntCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_int($value) && ! \is_string($value) && ! \is_float($value)) {
            return ConversionResult::error(new TypeError("int", type($value)));
        }
        if (\is_string($value) || \is_float($value)) {
            $validated = \filter_var((string)$value, FILTER_VALIDATE_INT);

            if ($validated === false) {
                return ConversionResult::error(new CoercingError("int", $value));
            }
            $value = $validated;
        }

        return ConversionResult::value($value);
    }
}

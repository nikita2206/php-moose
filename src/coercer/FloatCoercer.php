<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;

class FloatCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_float($value) && ! \is_int($value) && ! \is_string($value)) {
            return ConversionResult::error(new TypeError("float", \gettype($value)));
        }
        if (\is_string($value)) {
            $value = \filter_var($value, FILTER_VALIDATE_FLOAT);

            if ($value === false) {
                return ConversionResult::error(new CoercingError("float", $value));
            }
        }

        return ConversionResult::value((float)$value);
    }
}

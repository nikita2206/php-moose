<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class FloatCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_float($value) && ! \is_int($value) && ! \is_string($value)) {
            return ConversionResult::error(new TypeError("float", type($value)));
        }
        if (\is_string($value)) {
            $valid = \filter_var($value, FILTER_VALIDATE_FLOAT);

            if ($valid === false) {
                return ConversionResult::error(new CoercingError("float", $value));
            }
            $value = $valid;
        }

        return ConversionResult::value((float)$value);
    }
}

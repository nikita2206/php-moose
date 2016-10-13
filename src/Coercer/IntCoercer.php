<?php

namespace Moose\Coercer;

use Moose\Context;
use Moose\ConversionResult;
use Moose\Error\CoercingError;
use Moose\Error\TypeError;
use Moose\Metadata\TypeMetadata;

class IntCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_int($value) && ! \is_string($value) && ! \is_float($value)) {
            return ConversionResult::error(new TypeError("int", \gettype($value)));
        }
        if (\is_string($value) || \is_float($value)) {
            $value = \filter_var((string)$value, FILTER_VALIDATE_INT);

            if ($value === false) {
                return ConversionResult::error(new CoercingError("int", $value));
            }
        }

        return ConversionResult::value($value);
    }
}

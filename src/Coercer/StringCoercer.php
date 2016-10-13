<?php

namespace Moose\Coercer;

use Moose\Context;
use Moose\ConversionResult;
use Moose\Error\TypeError;
use Moose\Metadata\TypeMetadata;

class StringCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_string($value) && ! \is_int($value) && ! \is_float($value)) {
            return ConversionResult::error(new TypeError("string", \gettype($value)));
        }

        return ConversionResult::value((string)$value);
    }
}

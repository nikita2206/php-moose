<?php

namespace Moose\Coercer;

use Moose\Context;
use Moose\ConversionResult;
use Moose\Error\TypeError;
use Moose\Metadata\TypeMetadata;

class ObjectCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_array($value)) {
            return ConversionResult::error(new TypeError("object", \gettype($value)));
        }

        return $ctx->map($value, $metadata->args[0]);
    }
}

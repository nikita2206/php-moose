<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;

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

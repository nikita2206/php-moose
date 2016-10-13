<?php

namespace Moose\Coercer;

use Moose\Context;
use Moose\ConversionResult;
use Moose\Metadata\TypeMetadata;

interface TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult;
}

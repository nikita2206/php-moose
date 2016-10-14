<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\metadata\TypeMetadata;

interface TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult;
}

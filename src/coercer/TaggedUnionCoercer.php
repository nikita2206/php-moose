<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\InvalidTagError;
use moose\error\MissingFieldError;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class TaggedUnionCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_array($value)) {
            return ConversionResult::error(new TypeError("object", type($value)));
        }

        $tagField = $metadata->args[0];
        $map = $metadata->args[1];

        if ( ! isset($value[$tagField])) {
            return ConversionResult::error(new MissingFieldError($tagField));
        }

        $tag = $value[$tagField];

        if ( ! \is_string($tag) && ! \is_int($tag) && ! \is_float($tag)) {
            $tagCoerced = $ctx->coerce($tag, new TypeMetadata("string"));

            if ($tagCoerced->getErrors()) {
                return ConversionResult::errors($tagCoerced->errorsInField($tagField));
            }

            $tag = $tagCoerced->getValue();
        }

        if ( ! isset($map[$tag])) {
            return ConversionResult::error(new InvalidTagError($tag, \array_keys($map), $tagField));
        }

        $classnameOrMetadata = $map[$tag];

        if ($classnameOrMetadata instanceof TypeMetadata) {
            return $ctx->coerce($value, $classnameOrMetadata);
        } else {
            return $ctx->map($value, $classnameOrMetadata);
        }
    }
}

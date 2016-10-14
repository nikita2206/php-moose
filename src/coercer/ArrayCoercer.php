<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class ArrayCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_array($value)) {
            return ConversionResult::error(new TypeError("array", type($value)));
        }

        $errors = [];
        if ($metadata->args) {
            $type = $metadata->args[0]; /** @var TypeMetadata $type */
            $halfway = [];

            foreach ($value as $idx => $v) {
                $result = $ctx->coerce($v, $type);
                if ($result->getErrors()) {
                    $errors[] = $result->errorsAtIdx($idx);

                    if ($result->getValue() === null) {
                        return ConversionResult::errors(array_merge(...$errors));
                    }
                }

                $halfway[] = $result->getValue();
            }

            $value = $halfway;
        } else {
            $value = \array_values($value);
        }

        $errors = $errors ? array_merge(...$errors) : [];

        return ConversionResult::errors($errors, $value);
    }
}

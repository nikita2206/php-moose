<?php

namespace Moose\Coercer;

use Moose\Context;
use Moose\ConversionResult;
use Moose\Error\TypeError;
use Moose\Metadata\TypeMetadata;

class ArrayCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_array($value)) {
            return ConversionResult::error(new TypeError("array", \gettype($value)));
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

<?php

namespace Moose\Coercer;


use Moose\Context;
use Moose\ConversionResult;
use Moose\Error\TypeError;
use Moose\Metadata\TypeMetadata;

class MapCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_array($value)) {
            return ConversionResult::error(new TypeError("object", \gettype($value)));
        }

        $errors = [];
        if (\count($metadata->args) === 1) { // coerced values
            $type = $metadata->args[0]; /** @var TypeMetadata $type */
            $coerced = [];
            foreach ($value as $k => $v) {
                $result = $ctx->coerce($v, $type);
                if ($result->getErrors()) {
                    $errors[] = $result->errorsInField($k);

                    if ($result->getValue() === null) {
                        return ConversionResult::errors(array_merge(...$errors));
                    }
                }

                $coerced[$k] = $result->getValue();
            }

            $value = $coerced;
        } elseif (\count($metadata->args) === 2) { // coerced keys and values
            /** @var TypeMetadata $keyT */
            /** @var TypeMetadata $valueT */
            list($keyT, $valueT) = $metadata->args;

            $coerced = [];
            foreach ($value as $key => $val) {
                $v = $ctx->coerce($val, $valueT);
                $k = $ctx->coerce($key, $keyT);

                if ($v->getErrors() || $k->getErrors()) {
                    $errors[] = $v->errorsInField($key);
                    $errors[] = $k->errorsInField($key);

                    if ($v->getValue() === null || $k->getValue() === null) {
                        return ConversionResult::errors(array_merge(...$errors));
                    }
                }

                $coerced[$k->getValue()] = $v->getValue();
            }
            $value = $coerced;
        }

        $errors = $errors ? array_merge(...$errors) : [];

        return ConversionResult::errors($errors, $value);
    }
}

<?php

namespace moose;

use moose\coercer as co;
use moose\error as e;

function default_coercers(): array
{
    return [
        "array"  => new co\ArrayCoercer(),
        "bool"   => new co\BoolCoercer(),
        "date"   => new co\DateCoercer(),
        "float"  => new co\FloatCoercer(),
        "int"    => new co\IntCoercer(),
        "map"    => new co\MapCoercer(),
        "object" => new co\ObjectCoercer(),
        "string" => new co\StringCoercer()
    ];
}

/**
 * @param e\Error[] $errors
 * @return string[]
 */
function render_errors(array $errors): array
{
    $rendered = [];

    foreach ($errors as $error) {
        if ($error instanceof e\CoercingError) {
            $value = $error->getValue();
            $value = \is_string($value) || \is_numeric($value) ? $value : \gettype($value);
            $rendered[] = "CoercingError in field {$error->getField()}: expected {$error->getExpected()}, got {$value}";
        } elseif ($error instanceof e\InvalidDateFormatError) {
            $rendered[] = "InvalidDateFormatError in field {$error->getField()}: expected format {$error->getExpectedFormat()}, got {$error->getValue()}";
        } elseif ($error instanceof e\MissingFieldError) {
            $rendered[] = "MissingFieldError for field {$error->getField()}";
        } elseif ($error instanceof e\TypeError) {
            $rendered[] = "TypeError in field {$error->getField()}: expected {$error->getExpected()}, got {$error->getActual()}";
        } else {
            throw new \RuntimeException("There is no renderer for " . \get_class($error));
        }
    }

    return $rendered;
}

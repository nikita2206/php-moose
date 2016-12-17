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
        "string" => new co\StringCoercer(),
        "tagged_union" => new co\TaggedUnionCoercer(),
    ];
}

/**
 * This function is used for rendering errors in tests, it also represents an example of
 * how you could render errors for your kind of representation layer
 *
 * @param e\Error[] $errors
 * @return string[]
 */
function render_errors(array $errors): array
{
    $rendered = [];

    foreach ($errors as $error) {
        if ($error instanceof e\CoercingError) {
            $value = $error->getValue();
            $value = \is_string($value) || \is_numeric($value) ? $value : type($value);
            $rendered[] = "CoercingError in field {$error->getField()}: expected {$error->getExpected()}, got {$value}";
        } elseif ($error instanceof e\InvalidDateFormatError) {
            $rendered[] = "InvalidDateFormatError in field {$error->getField()}: expected format {$error->getExpectedFormat()}, got {$error->getValue()}";
        } elseif ($error instanceof e\MissingFieldError) {
            $rendered[] = "MissingFieldError for field {$error->getField()}";
        } elseif ($error instanceof e\TypeError) {
            $rendered[] = "TypeError in field {$error->getField()}: expected {$error->getExpected()}, got {$error->getActual()}";
        } elseif ($error instanceof e\InvalidTagError) {
            $expected = implode(", ", $error->getExpected());
            $rendered[] = "InvalidTagError in field {$error->getField()}: expected one of: {$expected}, got {$error->getTag()}";
        } else {
            throw new \RuntimeException("There is no renderer for " . \get_class($error));
        }
    }

    return $rendered;
}

/**
 * PHP is inconsistent with its type names. This function returns the most popular names for data types
 * instead of those that gettype() usually returns.
 *
 * @param $value
 * @return string
 */
function type($value): string
{
    static $map = [
        "boolean" => "bool",
        "integer" => "int",
        "double" => "float",
        "string" => "string",
        "array" => "array",
        "object" => "object",
        "resource" => "resource",
        "NULL" => "null",
        "unknown type" => "unknown type"
    ];

    return $map[\gettype($value)];
}

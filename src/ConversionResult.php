<?php

namespace Moose;

use Moose\Error\Error;

class ConversionResult
{
    private $value;

    /**
     * @var Error[]|null
     */
    private $errors;

    private function __construct($value, array $errors = null)
    {
        $this->value = $value;
        $this->errors = $errors;
    }

    public static function value($value)
    {
        return new ConversionResult($value, null);
    }

    public static function errors(array $errors, $value = null)
    {
        return new ConversionResult($value, $errors);
    }

    public static function error(Error $error, $value = null)
    {
        return new ConversionResult($value, [$error]);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function errorsInField($field): array
    {
        return \array_map(static function (Error $err) use ($field) {
            return $err->inField($field);
        }, $this->errors ?? []);
    }

    public function errorsAtIdx($idx): array
    {
        return \array_map(static function (Error $err) use ($idx) {
            return $err->atIndex($idx);
        }, $this->errors ?? []);
    }
}

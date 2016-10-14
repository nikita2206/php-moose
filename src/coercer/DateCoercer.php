<?php

namespace moose\coercer;

use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\InvalidDateFormatError;
use moose\metadata\TypeMetadata;

class DateCoercer implements TypeCoercer
{
    private $timezone;

    public function __construct(\DateTimeZone $tz = null)
    {
        $this->timezone = $tz ?: new \DateTimeZone(date_default_timezone_get());
    }

    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! is_string($value) && ! is_int($value)) {
            return ConversionResult::error(new CoercingError("string", $value));
        }

        $format = $metadata->args[0];

        $date = \DateTime::createFromFormat($format, $value, $this->timezone);
        if ( ! $date) {
            return ConversionResult::error(new InvalidDateFormatError($format, $value));
        }

        return ConversionResult::value($date);
    }
}

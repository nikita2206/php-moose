<?php

namespace moose\tests\unit\coercer;

use moose\coercer\DateCoercer;
use moose\coercer\TypeCoercer;
use moose\error\CoercingError;
use moose\error\InvalidDateFormatError;

class DateCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        yield ["1900-01-20", new \DateTime("1900-01-20"), $this->tm("date", ["Y-m-d|"])];

        yield [date(\DATE_ISO8601, 1), new \DateTime("@1"), $this->tm("date", [\DATE_ISO8601])];

        yield [150, new \DateTime("@150"), $this->tm("date", ["U"])];
    }

    public function failingScenarios()
    {
        yield ["1900-01-20", [new InvalidDateFormatError("d-m-Y|", "1900-01-20")], $this->tm("date", ["d-m-Y|"])];

        $anyType = $this->tm("date", ["d-m-Y"]);

        yield [[], [new CoercingError("string", [])], $anyType];

        yield [1.2, [new CoercingError("string", 1.2)], $anyType];

        yield [true, [new CoercingError("string", true)], $anyType];

        yield [null, [new CoercingError("string", null)], $anyType];

        yield [new \stdClass(), [new CoercingError("string", new \stdClass())], $anyType];
    }

    public function getCoercer(): TypeCoercer
    {
        return new DateCoercer();
    }
}

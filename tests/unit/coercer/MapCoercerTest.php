<?php

namespace moose\tests\unit\coercer;

use moose\coercer\MapCoercer;
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\TypeError;

class MapCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $obj = ["foo" => "bar", 1 => "b", 100 => new \stdClass(), 500 => [1]];
        yield [$obj, $obj, $this->tm("map")];

        $typeInt = $this->tm("int");
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(3))->method("coerce")
            ->withConsecutive([1, $typeInt], [2, $typeInt], [3, $typeInt])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::value(1)
              , ConversionResult::value(2)
              , ConversionResult::value(3));
        $origin = ["monkey" => "1", "donkey" => "2", "chimp" => "3"];

        yield [$origin, ["monkey" => 1, "donkey" => 2, "chimp" => 3], $this->tm("map", [$typeInt]), $ctx];

        $typeString = $this->tm("string");
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(4))->method("coerce")
            ->withConsecutive([100, $typeString], [99, $typeInt], ["foo", $typeString], [199, $typeInt])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::value("100")
              , ConversionResult::value(99)
              , ConversionResult::value("foo")
              , ConversionResult::value(199)
            );
        $origin = ["99" => 100, "199" => "foo"];

        yield [$origin, [99 => 100, 199 => "foo"], $this->tm("map", [$typeInt, $typeString]), $ctx];
    }

    public function failingScenarios()
    {
        yield [1, [new TypeError("object", "int")], $this->tm("map")];

        $anyType = $this->tm("any");

        $error = new TypeError("any", "not any");
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("coerce")
            ->with(100, $anyType)
            ->willReturn(ConversionResult::error($error));

        yield [["foo" => 100], [$error->inField("foo")], $this->tm("map", [$anyType]), null, $ctx];

        $err1 = new TypeError("any", "not any");
        $err2 = new CoercingError("any", "not any");

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(4))->method("coerce")
            ->withConsecutive([100, $anyType], [99, $anyType], [199, $anyType], [198, $anyType])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::error($err1, 100)
              , ConversionResult::error($err2, 99)
              , ConversionResult::value(199)
              , ConversionResult::value(198)
            );
        $origin = [99 => 100, 198 => 199];

        yield [$origin, [$err1->inField(99), $err2->inField(99)], $this->tm("map", [$anyType, $anyType]), $origin, $ctx];
    }

    public function getCoercer(): TypeCoercer
    {
        return new MapCoercer();
    }
}

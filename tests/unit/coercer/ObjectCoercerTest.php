<?php

namespace moose\tests\unit\coercer;

use moose\annotation\StringField;
use moose\coercer\ObjectCoercer;
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;

class ObjectCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $origin = ["foo" => "bar"];
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("map")
            ->with($origin, "stdClass")
            ->willReturn(ConversionResult::value((object)$origin));

        yield [$origin, (object)$origin, $this->tm("object", ["stdClass"]), $ctx];
    }

    public function failingScenarios()
    {
        yield [null, [new TypeError("object", "null")], $this->tm("object", [""])];

        $origin = ["foo" => "bar", "bar" => null];
        $result = new ObjectCoercerSubject();
        $result->foo = "bar";

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("map")
            ->with($origin, ObjectCoercerSubject::class)
            ->willReturn(ConversionResult::error(new TypeError("string", "null", "bar"), $result));

        yield [$origin, [new TypeError("string", "null", "bar")], $this->tm("object", [ObjectCoercerSubject::class]), $result, $ctx];
    }

    public function getCoercer(): TypeCoercer
    {
        return new ObjectCoercer();
    }
}

class ObjectCoercerSubject {
    /**
     * @StringField()
     */
    public $foo;
    /**
     * @StringField()
     */
    public $bar;
}

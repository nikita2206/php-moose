<?php

namespace moose\tests\unit\coercer;

use moose\coercer\TaggedUnionCoercer;
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\InvalidTagError;
use moose\error\MissingFieldError;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class TaggedUnionCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $origin = ["tag" => "foo", "qw" => 1, "cxv" => "wer"];

        $mapTm = $this->tm("map");
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("coerce")
            ->with($origin, $mapTm)->willReturn(ConversionResult::value($origin));

        $tm = $this->tm("tagged_union", ["tag", ["foo" => $mapTm, "bar" => "NonExistingClass"]]);

        yield [$input = $origin, $expectedOutput = $origin, $tm, $ctx];

        $origin = ["tag" => "bar", "bar" => 123, "foo" => 345];
        $expectedOutput = (object)$origin;

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("map")
            ->with($origin, "NonExistingClass")->willReturn(ConversionResult::value($expectedOutput));

        yield [$input = $origin, $expectedOutput, $tm, $ctx];

        $origin = ["tag" => ["bar"]];
        $expectedOutput = (object)$origin;

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("coerce")
            ->with(["bar"], new TypeMetadata("string"))->willReturn(ConversionResult::value("bar"));
        $ctx->expects($this->once())->method("map")
            ->with($origin, "NonExistingClass")->willReturn(ConversionResult::value($expectedOutput));

        yield [$input = $origin, $expectedOutput, $tm, $ctx];
    }

    public function failingScenarios()
    {
        $tm = $this->tm("tagged_union", ["tag", []]);
        foreach (["string", 123, 123.1, true] as $invalidData) {
            yield [$input = $invalidData, $errors = [new TypeError("object", type($invalidData))], $tm];
        }

        yield [$input = ["no_tag_here" => true], $errors = [new MissingFieldError("tag")], $tm];

        $tm = $this->tm("tagged_union", ["tag", ["foo" => "FooClass", "bar" => "BarClass"]]);

        yield [$input = ["tag" => "non-existing-tag"], $errors = [new InvalidTagError("non-existing-tag", ["foo", "bar"], "tag")], $tm];

        $error = new CoercingError("string", "array");
        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->once())->method("coerce")
            ->with([], new TypeMetadata("string"))->willReturn(ConversionResult::error($error));

        yield [$input = ["tag" => []], $errors = [$error->inField("tag")], $tm, null, $ctx];
    }

    public function getCoercer(): TypeCoercer
    {
        return new TaggedUnionCoercer();
    }
}

<?php

namespace moose\tests\unit\coercer;

use moose\coercer\ArrayCoercer;
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\CoercingError;
use moose\error\Error;
use moose\error\MissingFieldError;
use moose\error\TypeError;
use function moose\render_errors;

class ArrayCoercerTest extends CoercerTestCase
{
    public function getCoercer(): TypeCoercer
    {
        return new ArrayCoercer();
    }

    public function successfulScenarios()
    {
        $type = $this->tm("array");

        yield [[], [], $type];

        yield [[1, 2, "foo"], [1, 2, "foo"], $type];

        yield [[64 => 2, "foo", 256 => new \stdClass()], [2, "foo", new \stdClass()], $type];
    }

    public function failingScenarios()
    {
        $type = $this->tm("array");
        $typeInt = $this->tm("int");

        yield ["", [new TypeError("array", "string")], $type];

        $errors = [
            new CoercingError("int", 1)
          , new MissingFieldError()
          , new TypeError("int", "string")];
        $expectedErrors = array_map(function (Error $e, $idx) {
            return $e->atIndex($idx);
        }, $errors, array_keys($errors));

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(3))->method("coerce")
            ->withConsecutive([1, $typeInt], [2, $typeInt], [3, $typeInt])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::error($errors[0], 1)
              , ConversionResult::error($errors[1], 2)
              , ConversionResult::error($errors[2], 3));

        yield [[1, 2, 3], $expectedErrors, $this->tm("array", [$typeInt]), [1, 2, 3], $ctx];

        $errors = [
            new CoercingError("int", 1)
          , new MissingFieldError()];
        $expectedErrors = array_map(function (Error $e, $idx) {
            return $e->atIndex($idx);
        }, $errors, array_keys($errors));

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(2))->method("coerce")
            ->withConsecutive([1, $typeInt], [2, $typeInt])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::error($errors[0], 1)
              , ConversionResult::error($errors[1]));

        yield [[1, 2, 3], $expectedErrors, $this->tm("array", [$typeInt]), null, $ctx];
    }

    public function testTypedArraySuccess()
    {
        $input = [1, 2, 3];
        $typeInt = $this->tm("int");
        $type = $this->tm("array", [$typeInt]);

        $expectedOutput = [1, 2, 3];

        $ctx = $this->createMock(Context::class);
        $ctx->expects($this->exactly(3))
            ->method("coerce")
            ->withConsecutive([1, $typeInt], [2, $typeInt], [3, $typeInt])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::value(1)
              , ConversionResult::value(2)
              , ConversionResult::value(3));

        $coercer = new ArrayCoercer();

        $result = $coercer->coerce($input, $type, $ctx);
        if ($result->getErrors()) {
            $this->fail("Encountered errors: " . PHP_EOL . "  " . implode(PHP_EOL . "  ", render_errors($result->getErrors())));
        }

        $this->assertEquals($expectedOutput, $result->getValue());
    }
}

<?php

namespace moose\tests\unit\coercer;

use moose\coercer\FloatCoercer;
use moose\coercer\TypeCoercer;
use moose\error\CoercingError;
use moose\error\TypeError;

class FloatCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $type = $this->tm("float");

        yield [1, 1.0, $type];

        yield [PHP_INT_MAX, (float)PHP_INT_MAX, $type];

        yield [PHP_INT_MIN, (float)PHP_INT_MIN, $type];

        yield ["1.78", 1.78, $type];

        yield [(string)PHP_INT_MAX, (float)PHP_INT_MAX, $type];

        yield [1.99, 1.99, $type];
    }

    public function failingScenarios()
    {
        $type = $this->tm("float");

        yield ["foo", [new CoercingError("float", "foo")], $type];

        yield [false, [new TypeError("float", "bool")], $type];

        yield [true, [new TypeError("float", "bool")], $type];

        yield [[], [new TypeError("float", "array")], $type];

        yield [new \stdClass(), [new TypeError("float", "object")], $type];

        yield [null, [new TypeError("float", "null")], $type];
    }

    public function getCoercer(): TypeCoercer
    {
        return new FloatCoercer();
    }
}

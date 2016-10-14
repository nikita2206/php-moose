<?php

namespace moose\tests\unit\coercer;

use moose\coercer\IntCoercer;
use moose\coercer\TypeCoercer;
use moose\error\CoercingError;
use moose\error\TypeError;

class IntCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $type = $this->tm("int");

        yield [1, 1, $type];

        yield [(string)PHP_INT_MAX, PHP_INT_MAX, $type];

        yield [(string)PHP_INT_MIN, PHP_INT_MIN, $type];

        yield [100.0, 100, $type];
    }

    public function failingScenarios()
    {
        $type = $this->tm("int");

        yield ["foo", [new CoercingError("int", "foo")], $type];

        yield [[], [new TypeError("int", "array")], $type];

        yield [null, [new TypeError("int", "null")], $type];

        yield [false, [new TypeError("int", "bool")], $type];

        yield [true, [new TypeError("int", "bool")], $type];

        yield [100.1, [new CoercingError("int", 100.1)], $type];
    }

    public function getCoercer(): TypeCoercer
    {
        return new IntCoercer();
    }
}

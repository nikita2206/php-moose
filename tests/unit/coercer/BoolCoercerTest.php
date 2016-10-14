<?php

namespace moose\tests\unit\coercer;

use moose\coercer\BoolCoercer;
use moose\coercer\TypeCoercer;
use moose\error\TypeError;

class BoolCoercerTest extends CoercerTestCase
{
    public function successfulScenarios()
    {
        $type = $this->tm("bool");

        return [
            [true, true, $type]
          , [false, false, $type]
          , ["yes", true, $type]
          , ["no", false, $type]
          , ["y", true, $type]
          , ["n", false, $type]
          , ["true", true, $type]
          , ["false", false, $type]
          , [0, false, $type]
          , [1, true, $type]
        ];
    }

    public function failingScenarios()
    {
        $type = $this->tm("bool");

        yield [null, [new TypeError("bool", "null")], $type];

        yield [[], [new TypeError("bool", "array")], $type];
    }

    public function getCoercer(): TypeCoercer
    {
        return new BoolCoercer();
    }
}

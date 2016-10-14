<?php

namespace moose\tests\unit\coercer;

use moose\coercer\TypeCoercer;
use moose\Context;
use moose\metadata\TypeMetadata;
use PHPUnit\Framework\TestCase;
use function moose\render_errors;

abstract class CoercerTestCase extends TestCase
{
    /**
     * @dataProvider successfulScenarios
     */
    public function testSuccessfulScenarios($input, $expectedOutput, TypeMetadata $type, Context $ctx = null)
    {
        $coercer = $this->getCoercer();
        $ctx = $ctx ?? $this->createMock(Context::class);

        $result = $coercer->coerce($input, $type, $ctx);
        if ($result->getErrors()) {
            $this->fail("Encountered errors: " . PHP_EOL . "  " . implode(PHP_EOL . "  ", \moose\render_errors($result->getErrors())));
        }

        if (is_scalar($expectedOutput)) {
            $this->assertSame($expectedOutput, $result->getValue());
        } else {
            $this->assertEquals($expectedOutput, $result->getValue());
        }
    }

    /**
     * @dataProvider failingScenarios
     */
    public function testFailingScenarios($input, array $expectedErrors, TypeMetadata $type, $resultIfAny = null, Context $ctx = null)
    {
        $coercer = $this->getCoercer();
        $ctx = $ctx ?? $this->createMock(Context::class);

        $result = $coercer->coerce($input, $type, $ctx);
        if ( ! $result->getErrors()) {
            $this->fail("Got no errors, expected errors were: " . PHP_EOL . "  " . implode(PHP_EOL . "  ", render_errors($expectedErrors)));
        }

        $this->assertEquals($expectedErrors, $result->getErrors());
        $this->assertEquals($resultIfAny, $result->getValue());
    }

    abstract public function successfulScenarios();

    abstract public function failingScenarios();

    abstract public function getCoercer(): TypeCoercer;

    public function tm($type, $args = null)
    {
        $t = new TypeMetadata();
        $t->type = $type;
        $t->args = $args;

        return $t;
    }
}

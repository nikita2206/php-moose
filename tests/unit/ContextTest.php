<?php

namespace moose\tests\unit;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\MissingFieldError;
use moose\error\TypeError;
use moose\exception\CoercerNotDefinedException;
use moose\metadata\FieldMetadata;
use moose\metadata\MetadataProvider;
use moose\metadata\TypeMetadata;
use moose\Setters;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testCoerce()
    {
        $anyCoercer = $this->createMock(TypeCoercer::class);
        $ctx = $this->ctx(["any" => $anyCoercer]);

        $type = new TypeMetadata();
        $type->type = "any";

        $result = ConversionResult::value("foo");

        $anyCoercer->expects($this->once())->method("coerce")
            ->with("foo", $type, $ctx)
            ->willReturn($result);

        $this->assertSame($result, $ctx->coerce("foo", $type));
    }

    public function testCoerceFails()
    {
        $ctx = $this->ctx([]);

        $type = new TypeMetadata();
        $type->type = "any";

        try {
            $ctx->coerce("foo", $type);
        } catch (CoercerNotDefinedException $e) {
            $this->assertSame("any", $e->type);
            return;
        }

        $this->fail(CoercerNotDefinedException::class . " exception was expected, none was thrown");
    }

    public function testMap()
    {
        $coercer = $this->createMock(TypeCoercer::class);
        $provider = $this->createMock(MetadataProvider::class);

        $ctx = $this->ctx(["any" => $coercer], $provider, new Setters(), new Instantiator());

        $type = new TypeMetadata();
        $type->type = "any";

        $coercer->expects($this->exactly(3))->method("coerce")
            ->withConsecutive(["bar", $type, $ctx], [123, $type, $ctx], ["this one is remapped", $type, $ctx])
            ->willReturnOnConsecutiveCalls(
                ConversionResult::value(123)
              , ConversionResult::value("bar")
              , ConversionResult::error($typeError = new TypeError("any", "not any"), "this one is remapped")
            );

        $makeMd = function (string $field, bool $optional, string $origin = null) use ($type) {
            $md = new FieldMetadata();
            $md->type = $type;
            $md->field = $field;
            $md->origin = $origin ?? $field;
            $md->classname = ContextTestSubject::class;
            $md->optional = $optional;

            return $md;
        };

        $md1 = $makeMd("foo", false);
        $md2 = $makeMd("bar", false);
        $md3 = $makeMd("optional", true);
        $md4 = $makeMd("required", false);
        $md5 = $makeMd("remapped", false, "payload_name");

        $provider->expects($this->once())->method("for")
            ->with(ContextTestSubject::class)->willReturn([$md1, $md2, $md3, $md4, $md5]);

        $result = $ctx->map(["foo" => "bar", "bar" => 123, "payload_name" => "this one is remapped"], ContextTestSubject::class);

        $expected = new ContextTestSubject(123, "bar", null, null, "this one is remapped");
        $this->assertEquals($expected, $result->getValue());
        $this->assertEquals([
            new MissingFieldError("required")
          , $typeError->inField("payload_name")
        ], $result->getErrors());
    }

    public function ctx(array $coercers, MetadataProvider $provider = null, Setters $setters = null, InstantiatorInterface $instantiator = null)
    {
        $provider = $provider ?? $this->createMock(MetadataProvider::class);
        $setters = $setters ?? $this->createMock(Setters::class);
        $instantiator = $instantiator ?? $this->createMock(InstantiatorInterface::class);

        return new Context($provider, $setters, $instantiator, $coercers);
    }
}

class ContextTestSubject {
    public $foo, $bar, $optional, $required, $remapped;

    public function __construct($foo, $bar, $optional, $required, $remapped)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->optional = $optional;
        $this->required = $required;
        $this->remapped = $remapped;
    }
}

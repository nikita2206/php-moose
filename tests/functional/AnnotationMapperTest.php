<?php

namespace moose\tests\functional;

use Doctrine\Common\Annotations\AnnotationReader;
use moose\annotation as ann;
use moose\Mapper;
use moose\metadata\AnnotationMetadataProvider;
use PHPUnit\Framework\TestCase;
use function moose\default_coercers;
use function moose\render_errors;

class AnnotationMapperTest extends TestCase
{
    public function successfulMapping()
    {
        $e1 = new SimpleFlatStruct();
        $e1->untypedArray = [1, 2, "foo"];
        $e1->bool = true;
        $e1->date = new \DateTime();
        $e1->float = 1.5;
        $e1->int = 99;
        $e1->untypedMap = ["foo" => "bar", "bar" => 1, 1 => "foo"];
        $e1->string = "foo";

        $e2 = new NestedStruct();
        $e2->foo = "bar";
        $e2->simpleStruct = $e1;

        $e3 = new RecursiveStruct();
        $e3->bar = "foo";
        $e3->childs = (function () {
            $e1 = new RecursiveStruct();
            $e1->childs = [];

            $e2 = new RecursiveStruct();
            $e2->childs = (function () {
                $e1 = new RecursiveStruct();
                $e1->bar = "bar";
                $e1->childs = [];

                return [$e1];
            })();

            return [$e1, $e2];
        })();

        $o1 = ["array" => $e1->untypedArray, "bool" => "y", "date" => $e1->date->format("Y-m-d H:i:s"), "float" => "1.5", "int" => "99", "map" => $e1->untypedMap, "string" => "foo"];
        $o2 = ["foo" => "bar", "simpleStruct" => $o1];
        $o3 = ["bar" => "foo", "childs" => [
            ["childs" => []]
          , ["childs" => [["bar" => "bar", "childs" => []]]]
        ]];

        return [
            [SimpleFlatStruct::class     , $e1 , $o1 ]
          , [NestedStruct::class         , $e2 , $o2 ]
          , [RecursiveStruct::class      , $e3 , $o3 ]
        ];
    }

    /**
     * @dataProvider successfulMapping
     */
    public function testSuccessfulMapping($classname, $expected, $origin)
    {
        $metadata = new AnnotationMetadataProvider(new AnnotationReader());
        $mapper = new Mapper($metadata, default_coercers());

        $result = $mapper->map($origin, $classname);

        if ($result->getErrors()) {
            $this->fail("Encountered errors: " . PHP_EOL . "  " . implode(PHP_EOL . "  ", render_errors($result->getErrors())));
        }

        $this->assertEquals($expected, $result->getValue());
    }
}

class SimpleFlatStruct {
    /**
     * @ann\ArrayField(origin="array", optional=true)
     */
    public $untypedArray;
    /**
     * @ann\BoolField(optional=true)
     */
    public $bool;
    /**
     * @ann\DateField(format="Y-m-d H:i:s", optional=true)
     */
    public $date;
    /**
     * @ann\FloatField(optional=true)
     */
    public $float;
    /**
     * @ann\IntField(optional=true)
     */
    public $int;
    /**
     * @ann\MapField(origin="map", optional=true)
     */
    public $untypedMap;
    /**
     * @ann\StringField(optional=true)
     */
    public $string;
}

class NestedStruct {
    /**
     * @ann\StringField()
     */
    public $foo;
    /**
     * @ann\ObjectField("moose\tests\functional\SimpleFlatStruct")
     */
    public $simpleStruct;
}

class RecursiveStruct {
    /**
     * @ann\StringField(optional=true)
     */
    public $bar;
    /**
     * @ann\ArrayField(@ann\ObjectField("moose\tests\functional\RecursiveStruct"))
     */
    public $childs;
}

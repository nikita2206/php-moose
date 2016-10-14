<?php

namespace tests\integration;

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

        return [
            [SimpleFlatStruct::class, $e1, ["array" => $e1->untypedArray, "bool" => "y", "date" => $e1->date->format("Y-m-d H:i:s"), "float" => "1.5", "int" => "99", "map" => $e1->untypedMap, "string" => "foo"]]
          , 
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
     * @ann\ArrayField(origin="int", optional=true)
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

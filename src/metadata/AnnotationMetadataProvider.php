<?php

namespace moose\metadata;

use Doctrine\Common\Annotations\Reader;
use moose\annotation\exception\InvalidTypeException;
use moose\annotation\Field;
use moose\metadata\exception\InvalidAnnotationException;

class AnnotationMetadataProvider implements MetadataProvider
{
    const PROPERTY_TYPES = \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED;

    /**
     * @var Reader
     * @DateTime("Y-m-d")
     * @Array(T=@Array(T=@Array(T=@Array(@Map(K=@Int, V=@String)))))
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function for(string $classname): array
    {
        $refl = new \ReflectionClass($classname);
        $fields = [];

        do {
            foreach ($refl->getProperties(self::PROPERTY_TYPES) as $prop) {
                if ( ! $prop->isPrivate() && isset($fields[$prop->getName()])) {
                    continue;
                }

                $name = $prop->isPrivate() ? $refl->getName() . "#" . $prop->getName() : $prop->getName();

                $fields[$name] = $md = new FieldMetadata();
                $md->classname = $refl->getName();
                $md->field = $prop->getName();
                $md->origin = $prop->getName();

                try {
                    /** @var Field $type */
                    $type = $this->reader->getPropertyAnnotation($prop, Field::class);
                } catch (InvalidTypeException $e) {
                    $message = "Invalid annotation in {$refl->getName()}::{$prop->getName()}: {$e->getMessage()}";
                    throw new InvalidAnnotationException($message, 0, $e);
                }

                if ($type) {
                    $md->origin = $type->origin ?? $prop->getName();
                    $md->optional = $type->optional ?? false;
                    $md->type = $this->typeMetadataFromAnnotation($type);
                }
            }
        } while ($refl = $refl->getParentClass());

        return \array_values($fields);
    }

    private function typeMetadataFromAnnotation(Field $annot): TypeMetadata
    {
        $type = new TypeMetadata();
        $type->type = $annot->getTypeName();
        $type->args = $annot->getArgs() ? array_map(function ($arg) {
            return $arg instanceof Field ? $this->typeMetadataFromAnnotation($arg) : $arg;
        }, $annot->getArgs()) : null;

        return $type;
    }
}
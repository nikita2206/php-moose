<?php

namespace moose;

use Doctrine\Instantiator\InstantiatorInterface;
use moose\coercer\TypeCoercer;
use moose\error\MissingFieldError;
use moose\exception\CoercerNotDefinedException;
use moose\metadata\MetadataProvider;
use moose\metadata\TypeMetadata;

class Context
{
    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var Setters
     */
    private $setters;

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    /**
     * @var TypeCoercer[]
     */
    private $coercers;

    public function __construct(MetadataProvider $metadataProvider, Setters $setters, InstantiatorInterface $instantiator, array $coercers)
    {
        $this->metadataProvider = $metadataProvider;
        $this->setters = $setters;
        $this->instantiator = $instantiator;
        $this->coercers = $coercers;
    }

    public function coerce($value, TypeMetadata $metadata): ConversionResult
    {
        if ( ! isset($this->coercers[$metadata->type])) {
            throw new CoercerNotDefinedException($metadata->type);
        }

        return $this->coercers[$metadata->type]->coerce($value, $metadata, $this);
    }

    public function map(array $value, string $classname): ConversionResult
    {
        $metadata = $this->metadataProvider->for($classname);
        $instance = $this->instantiator->instantiate($classname);
        $errors = [];

        foreach ($metadata as $propertyMd) {
            if ( ! isset($value[$propertyMd->origin])) {
                if ( ! $propertyMd->optional) {
                    $errors[] = [new MissingFieldError($propertyMd->origin)];
                }
                continue;
            }

            if ($propertyMd->type) {
                $result = $this->coerce($value[$propertyMd->origin], $propertyMd->type);

                if ($result->getErrors()) {
                    $errors[] = $result->errorsInField($propertyMd->origin);
                }
                if ( ! ($result->getErrors() && $result->getValue() === null)) {
                    $setter = $this->setters->setter($propertyMd->classname);
                    $setter($instance, $propertyMd->field, $result->getValue());
                }
            } else {
                $setter = $this->setters->setter($propertyMd->classname);
                $setter($instance, $propertyMd->field, $value[$propertyMd->origin]);
            }
        }

        $errors = $errors ? array_merge(...$errors) : [];

        return ConversionResult::errors($errors, $instance);
    }
}

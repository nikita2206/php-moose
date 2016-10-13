<?php

namespace Moose;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Moose\Metadata\MetadataProvider;

class Mapper
{
    private $metadata;
    private $coercers;
    private $setters;
    private $instantiator;

    public function __construct(MetadataProvider $metadata, array $coercers,
                                Setters $setters = null, InstantiatorInterface $instantiator = null)
    {
        $this->metadata = $metadata;
        $this->coercers = $coercers;
        $this->setters = $setters ?: new Setters();
        $this->instantiator = $instantiator ?: new Instantiator();
    }

    public function map(array $value, string $classname): ConversionResult
    {
        $ctx = new Context($this->metadata, $this->setters, $this->instantiator, $this->coercers);

        return $ctx->map($value, $classname);
    }
}

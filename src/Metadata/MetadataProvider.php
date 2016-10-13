<?php

namespace Moose\Metadata;

interface MetadataProvider
{
    /**
     * @param string $classname
     * @return FieldMetadata[]
     */
    public function for(string $classname): array;
}

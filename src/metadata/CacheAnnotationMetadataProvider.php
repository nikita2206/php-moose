<?php

namespace moose\metadata;

use moose\metadata\exception\CacheMissException;

/**
 * It is different from CacheInvalidatingAnnotationMetadataProvider in that it will try to fetch data from cache
 * regardless of the last change of the file, and only if cache doesn't have the required entry it will regenerate it.
 * Because it doesn't need to know the source of the metadata (it can be annotations or xml config f.e.) it will work
 * with any pair of metadata providers
 */
class ProdCacheMetadataProvider implements MetadataProvider
{
    /**
     * @var CacheMetadataProvider
     */
    private $cacheProvider;

    /**
     * @var MetadataProvider
     */
    private $realProvider;

    public function __construct(CacheMetadataProvider $cacheProvider, MetadataProvider $realProvider)
    {
        $this->cacheProvider = $cacheProvider;
        $this->realProvider = $realProvider;
    }

    public function for(string $classname): array
    {
        try {
            return $this->cacheProvider->for($classname);
        } catch (CacheMissException $e) { }

        $metadata = $this->realProvider->for($classname);
        $this->cacheProvider->cache($classname, $metadata);

        return $metadata;
    }
}

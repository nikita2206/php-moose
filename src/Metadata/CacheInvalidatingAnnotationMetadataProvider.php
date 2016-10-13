<?php

namespace Moose\Metadata;

use Moose\Metadata\Exception\CacheMissException;

/**
 * Tries to fetch metadata from cache and checks if the cache entry is still fresh, otherwise regenerates it.
 */
class CacheInvalidatingAnnotationMetadataProvider implements MetadataProvider
{
    /**
     * @var MetadataProvider
     */
    private $realProvider;

    /**
     * @var CacheMetadataProvider
     */
    private $cacheProvider;

    public function __construct(MetadataProvider $realProvider, CacheMetadataProvider $cacheProvider)
    {
        $this->realProvider = $realProvider;
        $this->cacheProvider = $cacheProvider;
    }

    public function for(string $classname): array
    {
        $cachedAt = $this->cacheProvider->wasCachedAt($classname);

        if ($cachedAt === null) {
            goto miss;
        }

        $refl = new \ReflectionClass($classname);

        do {
            if (new \DateTime("@" . \filemtime($refl->getFileName())) > $cachedAt) {
                goto miss;
            }
        } while ($refl = $refl->getParentClass());

        try {
            return $this->cacheProvider->for($classname);
        } catch (CacheMissException $e) { }

        miss:
        $metadata = $this->realProvider->for($classname);
        $this->cacheProvider->cache($classname, $metadata);

        return $metadata;
    }
}

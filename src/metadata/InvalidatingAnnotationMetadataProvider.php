<?php

namespace moose\metadata;

use moose\metadata\exception\CacheMissException;

/**
 * Tries to fetch metadata from cache and checks if the cache entry is still fresh, otherwise regenerates it.
 */
class InvalidatingAnnotationMetadataProvider implements MetadataProvider
{
    /**
     * @var MetadataProvider
     */
    private $realProvider;

    /**
     * @var CacheMetadataProvider
     */
    private $cacheProvider;

    /**
     * @var array
     */
    private $checked;

    public function __construct(MetadataProvider $realProvider, CacheMetadataProvider $cacheProvider)
    {
        $this->realProvider = $realProvider;
        $this->cacheProvider = $cacheProvider;
        $this->checked = [];
    }

    public function for(string $classname): array
    {
        if ( ! isset($this->checked[$classname])) {
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
        }

        try {
            $this->checked[$classname] = true;
            return $this->cacheProvider->for($classname);
        } catch (CacheMissException $e) { }

        miss:
        $metadata = $this->realProvider->for($classname);
        $this->cacheProvider->cache($classname, $metadata);
        $this->checked[$classname] = true;

        return $metadata;
    }
}

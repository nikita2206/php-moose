<?php

namespace moose\metadata;

use Doctrine\Common\Cache\Cache;
use moose\metadata\exception\CacheMissException;

class CacheMetadataProvider implements MetadataProvider
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var \Closure
     */
    private $keyHash;

    /**
     * @var \Closure callable(): \DateTimeInterface
     */
    private $clock;

    public function __construct(Cache $cache, \Closure $keyHash = null, \Closure $clock = null)
    {
        $this->cache = $cache;
        $this->keyHash = $keyHash ?: function ($id) { return $id; };
        $this->clock = $clock ?: function () { return new \DateTime(); };
    }

    public function for(string $classname): array
    {
        $key = \call_user_func($this->keyHash, $classname);
        $result = $this->cache->fetch($key);
        if ( ! \is_array($result)) {
            throw new CacheMissException("Unexpected cache miss for key: {$key}");
        }

        return $result;
    }

    /**
     * @param string $classname
     * @return \DateTime|null
     */
    public function wasCachedAt(string $classname)
    {
        $key = $this->cachedTimestampKey(\call_user_func($this->keyHash, $classname));
        $result = $this->cache->fetch($key);

        return $result instanceof \DateTimeInterface ? $result : null;
    }

    public function cache(string $classname, array $metadata)
    {
        $key = \call_user_func($this->keyHash, $classname);
        $this->cache->save($key, $metadata);

        $key = $this->cachedTimestampKey($key);
        $this->cache->save($key, \call_user_func($this->clock));
    }

    protected function cachedTimestampKey($key): string
    {
        return $key . "_cached_at";
    }
}

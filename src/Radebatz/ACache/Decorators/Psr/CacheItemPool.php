<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Decorators\Psr;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Radebatz\ACache\CacheInterface;

/**
 * ACache Psr CacheItemPool implementation.
 */
class CacheItemPool implements CacheItemPoolInterface
{
    const BAD_KEY_CHARS = '{}()/\@:';

    /** @var $cache CacheInterface */
    protected $cache;
    protected $deferred;

    /**
     * Create a new cache item pool.
     *
     * @param $cache cacheInterface The underlying cache instance
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->deferred = [];
    }

    /**
     * Destroy cache item pool.
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function hasItem($key)
    {
        CacheItemPool::validateKey($key);

        if ($this->isDeferred($key)) {
            return $this->deferred[$key]->isHit();
        }

        return $this->cache->contains($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getItem($key)
    {
        CacheItemPool::validateKey($key);

        if (array_key_exists($key, $this->deferred)) {
            // clone to avoid changing queued item
            return clone $this->deferred[$key];
        }

        $ttl = null;
        $value = null;
        if ($this->cache->contains($key)) {
            $ttl = $this->cache->getTimeToLive($key);
            $value = $this->cache->fetch($key);
        }

        return new CacheItem($key, $value, $this, 0 === $ttl ? null : $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(array $keys = [])
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->cache->flush();
        $this->deferred = [];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItem($key)
    {
        CacheItemPool::validateKey($key);

        if ($isDeferred = $this->isDeferred($key)) {
            unset($this->deferred[$key]);
        }

        $isDeleted = !$this->cache->contains($key) || $this->cache->delete($key);

        return $isDeferred || $isDeleted;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItems(array $keys)
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $this->deleteItem($key) && $result;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function save(CacheItemInterface $item)
    {
        $expiresAt = $item->getExpiresAt();
        $expiresAt = ($expiresAt instanceof \DateTimeInterface) || ($expiresAt instanceof \DateTime) ? $expiresAt : null;


        return $this->cache->save($item->getKey(), $item->getValue(), null === $expiresAt ? 0 : $expiresAt->getTimestamp() - time());
    }

    /**
     * {@inheritDoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        return true;
    }

    /**
     * Get the underlying cache instance.
     *
     * @return CacheInterface the cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Check if there is a deferred cache item for the given key.
     *
     * @param mixed $key the key
     *
     * @return bool true if there is a deferred item for the key
     *
     * @throws InvalidArgumentException
     */
    public function isDeferred($key)
    {
        CacheItemPool::validateKey($key);

        return array_key_exists($key, $this->deferred);
    }

    /**
     * Validate key.
     *
     * @param mixed $key the key
     *
     * @throws InvalidArgumentException
     */
    public static function validateKey($key)
    {
        if (null == $key || is_numeric($key) || is_bool($key) || is_object($key) || is_array($key) || strpbrk($key, CacheItemPool::BAD_KEY_CHARS)) {
            if (null == $key) {
                $key = 'null';
            } elseif (is_bool($key)) {
                $key = 'bool(' . ($key ? 'true' : 'false') . ')';
            } elseif (is_object($key)) {
                $key = 'Object(' . get_class($key) . ')';
            } elseif (is_array($key)) {
                $key = 'Array';
            }
            throw new InvalidArgumentException(sprintf('Invalid key: %s', $key));
        }
    }
}

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
     * @param $cache CacheInterface The underlying cache instance.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->deferred = array();
    }

    /**
     * {@inheritDoc}
     */
    public function hasItem($key)
    {
        if (strpbrk($key, CacheItemPool::BAD_KEY_CHARS)) {
            throw new InvalidArgumentException(sprintf('Invalid key: %s', $key));
        }

        if (array_key_exists($key, $this->deferred)) {
            return true;
        }

        return $this->cache->contains($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getItem($key)
    {
        if (strpbrk($key, CacheItemPool::BAD_KEY_CHARS)) {
            throw new InvalidArgumentException(sprintf('Invalid key: %s', $key));
        }

        if (array_key_exists($key, $this->deferred)) {
            return $this->deferred[$key];
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
    public function getItems(array $keys = array())
    {
        $items = array();
        foreach ($keys as $key) {
            $items[] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->cache->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItem($key)
    {
        $this->cache->delete($key);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->cache->delete($key);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function save(CacheItemInterface $item)
    {
        $expiresAt = $item->getExpiresAt();
        $this->cache->save($item->getKey(), $item->getValue(), null === $expiresAt ? 0 : $expiresAt->getTimestamp() - time());

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        // TODO: improve $this->deferred[$item->getKey()] = $item;
        $this->save($item);

        return $this;
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
     * @return CacheInterface The cache.
     */
    public function getCache()
    {
        return $this->cache;
    }
}

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
        CacheItemPool::validateKey($key);

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
        CacheItemPool::validateKey($key);

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

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItem($key)
    {
        CacheItemPool::validateKey($key);

        return !$this->cache->contains($key) || $this->cache->delete($key);
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

        return $this->cache->save($item->getKey(), $item->getValue(), null === $expiresAt ? 0 : $expiresAt->getTimestamp() - time());
    }

    /**
     * {@inheritDoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        // TODO: improve $this->deferred[$item->getKey()] = $item;
        return $this->save($item);
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

    /**
     * Validate key.
     *
     * @param mixed $key The key.   
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

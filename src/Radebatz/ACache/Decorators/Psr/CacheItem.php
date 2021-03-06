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

/**
 * ACache Psr CacheItem implementation.
 */
class CacheItem implements CacheItemInterface
{
    protected $key;
    protected $value;
    /** @var CacheItemPool */
    protected $cacheItemPool;
    /** @var \DateTimeInterface */
    protected $expiresAt;

    /**
     * Create a new cache item.
     *
     * @throws InvalidArgumentException
     */
    public function __construct($key, $value, CacheItemPool $cacheItemPool, $ttl = null)
    {
        CacheItemPool::validateKey($key);

        $this->key = $key;
        $this->value = $value;
        $this->cacheItemPool = $cacheItemPool;
        $this->setExpiresAt($ttl);
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->isHit() ? $this->value : null;
    }

    /**
     * @inheritdoc
     */
    public function set($value, $ttl = null)
    {
        $this->value = $value;
        $this->setExpiresAt($ttl);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function isHit()
    {
        // underlying cache
        $cache = $this->cacheItemPool->getCache();
        $notStale = (null === ($expiresAt = $this->getExpiresAt())) || (time() < $expiresAt->getTimestamp());

        return ($cache->contains($this->key) || $this->cacheItemPool->isDeferred($this->key)) && $notStale;
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function expiresAt($expiration)
    {
        if (null === $expiration || ($expiration instanceof \DateTimeInterface) || ($expiration instanceof \DateTime)) {
            $this->setExpiresAt($expiration);

            return $this;
        }

        throw new InvalidArgumentException('Invalid expiration date');
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->setExpiresAt($time);

            return $this;
        } elseif ($time instanceof \DateInterval) {
            $now = new \DateTime();
            $this->setExpiresAt($now->add($time));

            return $this;
        } elseif (null === $time) {
            return $this;
        }

        throw new InvalidArgumentException('Invalid expiration time');
    }

    /**
     * Get the value irrespective of whether it is in cache or not.
     *
     * @return mixed the value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the expires at value.
     *
     * @return null|\DateTimeInterface the expires at date/time
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set expires at value from given ttl.
     *
     * @param int|DateTimeInterface $ttl
     */
    protected function setExpiresAt($ttl)
    {
        if (is_int($ttl)) {
            $this->expiresAt = new \DateTime('@' . (time() + $ttl));
        } elseif (($ttl instanceof \DateTimeInterface) || ($ttl instanceof \DateTime)) {
            $this->expiresAt = $ttl;
        } else {
            $this->expiresAt = null;
        }
    }
}

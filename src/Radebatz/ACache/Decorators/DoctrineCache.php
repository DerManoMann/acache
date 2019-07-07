<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Decorators;

use Doctrine\Common\Cache\Cache;
use Radebatz\ACache\CacheInterface;

/**
 * Doctrine cache interface decorator.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class DoctrineCache implements Cache
{
    protected $cache;

    /**
     * Create a decorator instance for the given cache.
     *
     * @param CacheInterface $cache the cache instance to decorate
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        if (!$this->contains($id)) {
            return false;
        }

        return $this->cache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}

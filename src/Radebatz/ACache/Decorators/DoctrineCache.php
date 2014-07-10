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
     * @param Radebatz\ACache\CacheInterface $cache The cache instance to decorate.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }

}

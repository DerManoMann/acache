<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache;

/**
 * Null cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class NullCache implements CacheInterface
{

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $entry, $lifeTime = 0, $namespace = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return array(
            CacheInterface::STATS_SIZE => 0,
        );
    }

}

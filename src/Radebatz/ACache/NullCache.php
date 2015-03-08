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
    protected $available;

    /**
     * Create new instance.
     *
     * @param boolean $available Availability of this instance.
     */
    public function __construct($available = true)
    {
        $this->available = $available;
    }

    /**
     * {@inheritDoc}
     */
    public function available()
    {
        return $this->available;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        return;
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
    public function getDefaultTimeToLive()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $entry, $lifeTime = null, $namespace = null)
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

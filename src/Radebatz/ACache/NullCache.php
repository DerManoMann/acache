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
     * @param bool $available availability of this instance
     */
    public function __construct($available = true)
    {
        $this->available = $available;
    }

    /**
     * {@inheritdoc}
     */
    public function available()
    {
        return $this->available;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id, $namespace = null)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id, $namespace = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTimeToLive()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $entry, $lifeTime = null, $namespace = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, $namespace = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return [
            CacheInterface::STATS_SIZE => 0,
        ];
    }
}

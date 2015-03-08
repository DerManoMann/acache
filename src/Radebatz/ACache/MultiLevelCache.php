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

use InvalidArgumentException;

/**
 * Multi level cache.
 *
 * Using the $<code>$bubbleOnFetch</code> option allows to restore data from a lower stack element
 * in all higher elements again. The life time will be adjusted accordingly, however there might
 * be rounding differences (up to a second).
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class MultiLevelCache implements CacheInterface
{
    protected $stack;
    protected $bubbleOnFetch;

    /**
     * Create new instance for the given cache instances.
     *
     * @param array   $stack         List of <code>CacheInterface</code> instances.
     * @param boolean $bubbleOnFetch Optional flag to restore cache entries further up the stack if an item was only found further down.
     */
    public function __construct(array $stack = array(), $bubbleOnFetch = false)
    {
        if (!$stack) {
            throw new InvalidArgumentException('Need at least one cache in the stack');
        }

        foreach ($stack as $cache) {
            if (!($cache instanceof CacheInterface)) {
                throw new InvalidArgumentException('All stack elements must implement the Cache interface');
            }
        }

        $this->stack = array_filter($stack, function ($cache) { return $cache->available(); });
        $this->bubbleOnFetch = $bubbleOnFetch;
    }

    /**
     * {@inheritDoc}
     */
    public function available()
    {
        return 0 < count($this->stack);
    }

    /**
     * Get the cache stack.
     *
     * @return array List of <code>CacheInterface</code> instances.
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Returns the <code>bubbleOnFetch</code> flag.
     *
     * @return boolean <code>true</code> if bubble on fetch is enabled, <code>false</code> if not.
     */
    public function isBubbleOnFetch()
    {
        return $this->bubbleOnFetch;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        foreach ($this->stack as $ii => $cache) {
            if (null !== ($data = $cache->fetch($id, $namespace))) {
                if ($this->bubbleOnFetch && $ii) {
                    // remaining time to live
                    $timeToLive = $cache->getTimeToLive($id, $namespace);
                    do {
                        $this->stack[--$ii]->save($id, $data, $timeToLive, $namespace);
                    } while ($ii);
                }

                return $data;
            }
        }

        return;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        foreach ($this->stack as $cache) {
            if ($cache->contains($id, $namespace)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        foreach ($this->stack as $cache) {
            if (false !== ($timeToLive = $cache->getTimeToLive($id, $namespace))) {
                return $timeToLive;
            }
        }

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
    public function save($id, $data, $lifeTime = null, $namespace = null)
    {
        foreach ($this->stack as $cache) {
            if (!$cache->save($id, $data, $lifeTime, $namespace)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        foreach ($this->stack as $cache) {
            if (!$cache->delete($id, $namespace)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        foreach ($this->stack as $cache) {
            if (!$cache->flush($namespace)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        $stats = array();
        foreach ($this->stack as $cache) {
            $stats[] = $cache->getStats();
        }

        return $stats;
    }
}

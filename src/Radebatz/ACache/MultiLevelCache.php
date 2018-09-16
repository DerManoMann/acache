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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Create new instance for the given cache instances.
     *
     * @param array           $stack         list of <code>CacheInterface</code> instances
     * @param bool            $bubbleOnFetch optional flag to restore cache entries further up the stack if an item was only found further down
     * @param LoggerInterface $logger        optional logger
     */
    public function __construct(array $stack = [], $bubbleOnFetch = false, LoggerInterface $logger = null)
    {
        $logger = $this->logger = $logger ?: new NullLogger();

        $this->stack = array_filter($stack, function ($cache) use ($logger) {
            if (!($cache instanceof CacheInterface)) {
                $this->logger->warning(sprintf('Invalid cache - removing from stack: %s', is_object($cache) ? get_class($cache) : 'non_an_object'));

                return false;
            }
            if (!$cache->available()) {
                $logger->warning(sprintf('Cache not available - removing from stack: %s', get_class($cache)));

                return false;
            }

            return true;
        });

        // fill index holes
        $this->stack = array_values($this->stack);

        if (!$this->stack) {
            $this->logger->warning('Empty stack');
        }

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
     * @return array list of <code>CacheInterface</code> instances
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Returns the <code>bubbleOnFetch</code> flag.
     *
     * @return bool <code>true</code> if bubble on fetch is enabled, <code>false</code> if not
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
        /** @var $cache CacheInterface */
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

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        /** @var $cache CacheInterface */
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
        /** @var $cache CacheInterface */
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
        /** @var $cache CacheInterface */
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
        /** @var $cache CacheInterface */
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
        /** @var $cache CacheInterface */
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
        $stats = [];

        /** @var $cache CacheInterface */
        foreach ($this->stack as $cache) {
            $stats[] = $cache->getStats();
        }

        return $stats;
    }
}

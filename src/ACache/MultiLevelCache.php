<?php
namespace ACache;

use InvalidArgumentException;

/**
 * Multi level cache.
 *
 * The <code>$bubbleOnFetch</code> option will re-add mssing
 * Using the $<code>$bubbleOnFetch</code> option allows to restore data from a lower stack element
 * in all higher elements again. The life time will be adjusted accordingly, however there might
 * be rounding differences (up to a second).
 */
class MultiLevelCache implements Cache {
    protected $stack;
    protected $bubbleOnFetch;


    /**
     * Create new instance for the given cache instances.
     *
     * @param array $stack List of <code>Cache</code> instances; default is an empty array.
     * @param boolean $bubbleOnFetch Optional flag to restore cache entries further up the stack if an item was
     *  only found further down; default is <code>false</code>.
     */
    public function __construct(array $stack = array(), $bubbleOnFetch = false) {
        if (!$stack) {
            throw new InvalidArgumentException('Need at least one cache in the stack');
        }

        foreach ($stack as $cache) {
            if (!($cache instanceof Cache)) {
                throw new InvalidArgumentException('All stack elements must implement the Cache interface');
            }
        }
        $this->stack = $stack;
        $this->bubbleOnFetch = $bubbleOnFetch;
    }


    /**
     * Returns the <code>bubbleOnFetch</code> flag.
     *
     * @return boolean <code>true</code> if bubble on fetch is enabled, <code>false</code> if not.
     */
    public function isBubbleOnFetch() {
        return $this->bubbleOnFetch;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id) {
        foreach ($this->stack as $ii => $cache) {
            if (null !== ($data = $cache->fetch($id))) {
                if ($this->bubbleOnFetch && $ii) {
                    // remaining time to live
                    $timeToLive = $this->getTimeToLive($id);
                    do {
                        $this->stack[--$ii]->save($id, $data, $timeToLive);
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
    public function contains($id) {
        foreach ($this->stack as $cache) {
            if ($cache->contains($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id) {
        foreach ($this->stack as $cache) {
            if (false !== ($timeToLive = $cache->getTimeToLive($id))) {
                return $timeToLive;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0) {
        foreach ($this->stack as $cache) {
            if (!$cache->save($id, $data, $lifeTime)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        foreach ($this->stack as $cache) {
            if (!$cache->delete($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
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
    public function getStats() {
        $stats = array();
        foreach ($this->stack as $cache) {
            $stats[] = $cache->getStats();
        }

        return $stats;
    }

}

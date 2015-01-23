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
 * APC cache.
 *
 * Since APC does not remove expired entries automatically, the cache can be configured to trigger a
 * GC/cleanup run if any of the configured thresholds are reached.
 * Futhermore, there may be separate conditions configured to completely clear the APC cache (to clear fragmentation).
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ApcCache extends AbstractPathKeyCache
{
    protected $gc;

    /**
     * Create instance.
     *
     * @param int   $defaultTimeToLive Optional default time-to-live value.
     * @param ApcGC $gc Optional garbage collector.
     */
    public function __construct($defaultTimeToLive = 0, $gc = null)
    {
        parent::__construct(self::DEFAULT_NAMESPACE_DELIMITER, $defaultTimeToLive);

        $this->gc = $gc && ($gc instanceof ApcGC) ? $gc : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        return apc_fetch($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return apc_exists($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        if ($this->gc) {
            $this->gc->run();
        }

        return (bool) apc_store($id, $entry, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        return apc_delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            return apc_clear_cache('user');
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);

            // iterate over all entries and delete matching
            $cacheInfo = apc_cache_info('user');
            foreach ($cacheInfo['cache_list'] as $entry) {
                if (0 === strpos($entry['info'], $namespace)) {
                    apc_delete($entry['info']);
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        $cacheInfo = apc_cache_info('user');
        $smaInfo = apc_sma_info();

        // @TODO - Temporary fix @see https://github.com/krakjoe/apcu/pull/42 AND hhvm compat
        if (PHP_VERSION_ID >= 50500) {
            // apcu + hhvm
            $cacheInfo['num_hits'] = isset($cacheInfo['nhits']) ? $cacheInfo['num_hits'] : isset($cacheInfo['num_hits']) ? $cacheInfo['num_hits'] : 0;
            $cacheInfo['num_misses'] = isset($cacheInfo['nmisses']) ? $cacheInfo['num_misses'] : isset($cacheInfo['num_misses']) ? $cacheInfo['num_misses'] : 0;
            $cacheInfo['start_time'] = isset($cacheInfo['stime']) ? $cacheInfo['start_time'] : isset($cacheInfo['start_time']) ? $cacheInfo['start_time'] : 0;

            // hhvm
            $cacheInfo['mem_size'] = isset($cacheInfo['mem_size']) ? $cacheInfo['mem_size'] : 0;
            $smaInfo['avail_mem'] = isset($smaInfo['avail_mem']) ? $smaInfo['avail_mem'] : 0;
        }

        return array(
            CacheInterface::STATS_SIZE => count($cacheInfo['cache_list']),
            CacheInterface::STATS_HITS => $cacheInfo['num_hits'],
            CacheInterface::STATS_MISSES => $cacheInfo['num_misses'],
            CacheInterface::STATS_UPTIME => $cacheInfo['start_time'],
            CacheInterface::STATS_MEMORY_USAGE => $cacheInfo['mem_size'],
            CacheInterface::STATS_MEMORY_AVAILIABLE => $smaInfo['avail_mem'],
        );
    }

}

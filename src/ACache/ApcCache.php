<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ACache;

/**
 * APC cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ApcCache extends AbstractPathKeyCache
{

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

        return array(
            Cache::STATS_SIZE => count($cacheInfo['cache_list']),
            Cache::STATS_HITS => $cacheInfo['num_hits'],
            Cache::STATS_MISSES => $cacheInfo['num_misses'],
            Cache::STATS_UPTIME => $cacheInfo['start_time'],
            Cache::STATS_MEMORY_USAGE => $cacheInfo['mem_size'],
            Cache::STATS_MEMORY_AVAILIABLE => $smaInfo['avail_mem'],
        );
    }

}

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
 * Wincache cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class WincacheCache extends AbstractPathKeyCache
{

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        $entry = wincache_ucache_get($id, $success);
        return $success ? $entry : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return wincache_ucache_exists($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        return wincache_ucache_set($id, $entry, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        return wincache_ucache_delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            return wincache_ucache_clear();
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
            $info = wincache_ucache_info(false);
            // iterate over all entries and delete matching
            foreach ($info['ucache_entries'] as $entry) {
                $key = $entry['key_name'];
                if (0 === strpos($key, $namespace)) {
                    wincache_ucache_delete($key);
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
        $info = wincache_ucache_info(true);
        $meminfo = wincache_ucache_meminfo();

        return array(
            Cache::STATS_SIZE => $info['total_item_count'],
            Cache::STATS_HITS => $info['total_hit_count'],
            Cache::STATS_MISSES => $info['total_miss_count'],
            Cache::STATS_UPTIME => $info['total_cache_uptime'],
            Cache::STATS_MEMORY_USAGE => $meminfo['memory_total'],
            Cache::STATS_MEMORY_AVAILIABLE => $meminfo['memory_free'],
        );
    }

}
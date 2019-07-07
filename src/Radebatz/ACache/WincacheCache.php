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
 * Wincache cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class WincacheCache extends AbstractPathKeyCache
{
    /**
     * {@inheritdoc}
     */
    public function available()
    {
        return function_exists('wincache_ucache_exists');
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchEntry($id)
    {
        $entry = wincache_ucache_get($id, $success);

        return $success ? $entry : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function containsEntry($id)
    {
        return wincache_ucache_exists($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        return wincache_ucache_set($id, $entry, (int) $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntry($id)
    {
        return wincache_ucache_delete($id);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getStats()
    {
        $info = wincache_ucache_info(true);
        $meminfo = wincache_ucache_meminfo();

        return [
            CacheInterface::STATS_SIZE => $info['total_item_count'],
            CacheInterface::STATS_HITS => $info['total_hit_count'],
            CacheInterface::STATS_MISSES => $info['total_miss_count'],
            CacheInterface::STATS_UPTIME => $info['total_cache_uptime'],
            CacheInterface::STATS_MEMORY_USAGE => $meminfo['memory_total'],
            CacheInterface::STATS_MEMORY_AVAILIABLE => $meminfo['memory_free'],
        ];
    }
}

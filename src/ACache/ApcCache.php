<?php
namespace ACache;

/**
 * APC cache.
 */
class ApcCache implements Cache {

    /**
     * Create instance.
     *
     * @param array $data Optional initial cache data; default is an empty array.
     */
    public function __construct(array $data = array()) {
        $this->data = $data;
    }


    /**
     * {@inheritDoc}
     */
    public function fetch($id) {
        if (!$this->contains($id)) {
            return null;
        }

        $entry = apc_fetch($id);

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id) {
        return apc_exists($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id) {
        if ($this->contains($id)) {
            $entry = apc_fetch($id);
            return $entry['expires'] ? ($entry['expires'] - time()) : 0;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0) {
        $entry = array('data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0));

        return (bool) apc_store($id, $entry, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        return apc_delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
        if (!$namespace) {
            return apc_clear_cache('user');
        } else {
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
    public function getStats() {
        $cache = apc_cache_info();
        $sma  = apc_sma_info();

        return array(
            Cache::STATS_HITS => $cache['num_hits'],
            Cache::STATS_MISSES => $cache['num_misses'],
            Cache::STATS_UPTIME => $cache['start_time'],
            Cache::STATS_MEMORY_USAGE => $cache['mem_size'],
            Cache::STATS_MEMORY_AVAILIABLE => $sma['avail_mem'],
        );
    }

}

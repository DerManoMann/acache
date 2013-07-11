<?php
namespace ACache;

/**
 * APC cache.
 */
class ApcCache implements Cache
{
    const NAMESPACE_DELIMITER = '==';

    /**
     * Convert id and namespace to string.
     *
     * @param  string       $id        The id.
     * @param  string|array $namespace The namespace.
     * @return string       The namespace as string.
     */
    protected function namespaceId($id, $namespace)
    {
        $tmp = (array) $namespace;
        $tmp[] = $id;

        return implode(static::NAMESPACE_DELIMITER, $tmp);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        if (!$this->contains($id, $namespace)) {
            return null;
        }

        $entry = apc_fetch($this->namespaceId($id, $namespace));

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        return apc_exists($this->namespaceId($id, $namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        if ($this->contains($id, $namespace)) {
            $entry = apc_fetch($this->namespaceId($id, $namespace));

            return $entry['expires'] ? ($entry['expires'] - time()) : 0;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $namespace = null, $lifeTime = 0)
    {
        $entry = array('data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0));

        return (bool) apc_store($this->namespaceId($id, $namespace), $entry, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return apc_delete($this->namespaceId($id, $namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            return apc_clear_cache('user');
        } else {
            $namespace = implode(static::NAMESPACE_DELIMITER, (array) $namespace);
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

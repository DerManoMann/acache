<?php
namespace ACache;

/**
 * Cache interface
 */
interface Cache {
    const STATS_HITS = 'hits';
    const STATS_MISSES = 'misses';
    const STATS_UPTIME = 'uptime';
    const STATS_MEMORY_USAGE = 'memory_usage';
    const STATS_MEMORY_AVAILIABLE = 'memory_available';


    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     * @return string The cached data or <code>null</code>, if no cache entry exists for the given id.
     */
    function fetch($id);

    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     * @return boolean <code>true</code> if a cache entry exists for the given cache id, <code>false</code> if not.
     */
    function contains($id);

    /**
     * Returns the (remaining) time to live for the cache id.
     *
     * @param string $id The cache id.
     * @return false|int The time to live in seconds or <code>false</code> if the given id does not exist in the cache.
     */
    function getTimeToLive($id);

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache data.
     * @param int $lifeTime The lifetime in seconds. Set to 0 for infinite life time; default is 0.
     * @return boolean <code>true</code> if the entry was successfully stored in the cache, <code>false</code> if not.
     */
    function save($id, $data, $lifeTime = 0);

    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean <code>true</code> if the entry was successfully deleted from the cache, <code>false</code> if not.
     */
    function delete($id);

    /**
     * Flushes parts or all of the cache.
     *
     * @param string $namespace A <em>namespace</em> to limit flushing to a particular part of the cache; default is <code>null</code> for all.
     * @return boolean <code>true</code> if the cache was successfully flushed, <code>false</code> if not.
     */
    function flush($namespace = null);

    /**
     * Retrieves cache stats.
     *
     * Optional and may not be implemented by all cache classes.
     *
     * This method is somewhat stolen from the doctrine cache code.
     *
     * @return array Map with cache stats.
     */
    function getStats();

}

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
 * Cache interface.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
interface CacheInterface
{
    const STATS_SIZE = 'size';
    const STATS_HITS = 'hits';
    const STATS_MISSES = 'misses';
    const STATS_UPTIME = 'uptime';
    const STATS_MEMORY_USAGE = 'memory_usage';
    const STATS_MEMORY_AVAILIABLE = 'memory_available';

    /**
     * Checks if the actual cache implementation (ie the backend used) is available.
     *
     * @return boolean <code>true</code> if this cache implementation is available.
     */
    public function available();

    /**
     * Fetches an entry from the cache.
     *
     * @param string       $id        The id of the cache entry to fetch.
     * @param string|array $namespace Optional namespace; default is <code>null</code> for none.
     *
     * @return mixed The cached data or <code>null</code>, if no cache entry exists for the given id.
     */
    public function fetch($id, $namespace = null);

    /**
     * Test if an entry exists in the cache.
     *
     * @param string       $id        The cache id of the entry to check for.
     * @param string|array $namespace Optional namespace; default is <code>null</code> for none.
     *
     * @return boolean <code>true</code> if a cache entry exists for the given cache id, <code>false</code> if not.
     */
    public function contains($id, $namespace = null);

    /**
     * Returns the (remaining) time to live for the cache id.
     *
     * @param string       $id        The cache id.
     * @param string|array $namespace Optional namespace; default is <code>null</code> for none.
     *
     * @return false|int The time to live in seconds or <code>false</code> if the given id does not exist in the cache.
     */
    public function getTimeToLive($id, $namespace = null);

    /**
     * Returns the time to live default configured for this cache instance.
     *
     * @return int The default time to live value to be used if <code>lifeTime</code> is set to <code>null</code>.
     */
    public function getDefaultTimeToLive();

    /**
     * Puts data into the cache.
     *
     * @param string       $id        The cache id.
     * @param mixed        $data      The cache data.
     * @param int          $lifeTime  The lifetime in seconds. Set to 0 for infinite life time;
     *                                default is <code>null</code> to use the configured default life time.
     * @param string|array $namespace Optional namespace; default is <code>null</code> for none.
     *
     * @return boolean <code>true</code> if the entry was successfully stored in the cache, <code>false</code> if not.
     */
    public function save($id, $data, $lifeTime = null, $namespace = null);

    /**
     * Deletes a cache entry.
     *
     * @param string       $id        cache id
     * @param string|array $namespace Optional namespace; default is <code>null</code> for none.
     *
     * @return boolean <code>true</code> if the entry was successfully deleted from the cache, <code>false</code> if not.
     */
    public function delete($id, $namespace = null);

    /**
     * Flushes parts or all of the cache.
     *
     * @param string|array $namespace Optional namespace; default is <code>null</code> for all.
     *
     * @return boolean <code>true</code> if the cache was successfully flushed, <code>false</code> if not.
     */
    public function flush($namespace = null);

    /**
     * Retrieves cache stats.
     *
     * Optional and may not be implemented by all cache classes.
     *
     * Calling this method also might not be side-effect free as implementations might
     * do some cache clean-up first in order to generate accurate numbers.
     *
     * This method is somewhat borrowed from the doctrine cache code in that it used the same stats keys.
     *
     * @return array Map with cache stats.
     */
    public function getStats();
}

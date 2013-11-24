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

use Memcache;

/**
 * Memcache cache.
 *
 * Configuration options are:
 *
 *   * <code>host</code>: The memcache hostname; default is <code>localhost</code>.
 *   * <code>port</code>: The memcache port; default is <codGcode>.
 *   * <code>compress</code>: Whether to compress data or not; default is <code>false</code>.
 *
 * Flushing a namespace relies on the memcache <em>cachedump</em> command which is subject to change / removal.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 * @see http://www.php.net/manual/en/memcache.getstats.php
 */
class MemcacheCache extends AbstractPathKeyCache
{
    protected $memcache;
    protected $compress;

    /**
     * Create instance.
     *
     * @param array $config Optional config settings; default is an empty array.
     */
    public function __construct(array $config = array())
    {
        parent::__construct();

        $this->memcache = new Memcache();
        // merge with some defaults
        $config = array_merge(
            array(
                'host' => 'localhost',
                'port' => 11211,
                'compress' => false,
            ),
            $config
        );
        $this->memcache->connect($config['host'], $config['port']);
        $this->compress = $config['compress'] ? MEMCACHE_COMPRESSED : 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        return $this->memcache->get($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return false !== $this->memcache->get($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        return $this->memcache->set($id, $entry, $this->compress, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        return $this->memcache->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            return $this->memcache->flush();
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
            // iterate over all entries and delete matching
            foreach ($this->memcache->getExtendedStats('items slabs') as $summary) {
                foreach ($summary as $slabDetails) {
                    foreach ($slabDetails as $slabId => $details) {
                        $slabItems = $this->memcache->getExtendedStats('cachedump', $slabId, $details['number']);
                        foreach ($slabItems as $server => $items) {
                            foreach ($items as $key => $item) {
                                if (0 === strpos($key, $namespace)) {
                                    $this->memcache->delete($key);
                                }
                            }
                        }
                    }
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
        $stats = $this->memcache->getStats();

        return array(
            Cache::STATS_SIZE => $stats['curr_items'],
            Cache::STATS_HITS => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILIABLE => $stats['limit_maxbytes'],
        );
    }

}

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
 *  * <code>host</code>:     The memcache hostname; default is <code>localhost</code>.
 *  * <code>port</code>:     The memcache port; default is <codGcode>.
 *  * <code>compress</code>: Whether to compress data or not; default is <code>false</code>.
 *
 * Flushing a namespace relies on the memcache <em>cachedump</em> command which is subject to change / removal.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 *
 * @see http://www.php.net/manual/en/memcache.getstats.php
 */
class MemcacheCache extends AbstractPathKeyCache
{
    protected $memcache;
    protected $compress;

    /**
     * Create instance.
     *
     * @param array $config            optional config settings; default is an empty array
     * @param int   $defaultTimeToLive optional default time-to-live value
     */
    public function __construct(array $config = [], $defaultTimeToLive = 0)
    {
        parent::__construct(self::DEFAULT_NAMESPACE_DELIMITER, $defaultTimeToLive);

        if (class_exists('Memcache')) {
            $this->memcache = new Memcache();
            // merge with some defaults
            $config = array_merge(
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'compress' => false,
                ],
                $config
            );
            if (!($connected = @$this->memcache->connect($config['host'], $config['port']))) {
                $this->memcache = null;
            }
        } else {
            $this->memcache = null;
        }

        $this->compress = (array_key_exists('compress', $config) && $config['compress'] && defined('MEMCACHE_COMPRESSED')) ? MEMCACHE_COMPRESSED : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function available()
    {
        return null !== $this->memcache;
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
            $slabs = $this->memcache->getExtendedStats('slabs');
            $items = $this->memcache->getExtendedStats('items');
            foreach ($slabs as $server => $serverSlabs) {
                foreach ($serverSlabs as $slabId => $slabMeta) {
                    if (is_int($slabId)) {
                        $cdump = $this->memcache->getExtendedStats('cachedump', $slabId);
                        foreach ($cdump as $values) {
                            if (is_array($values)) {
                                foreach ($values as $key => $value) {
                                    if (0 === strpos($key, $namespace)) {
                                        $this->memcache->delete($key);
                                    }
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

        return [
            CacheInterface::STATS_SIZE => $stats['curr_items'],
            CacheInterface::STATS_HITS => $stats['get_hits'],
            CacheInterface::STATS_MISSES => $stats['get_misses'],
            CacheInterface::STATS_UPTIME => $stats['uptime'],
            CacheInterface::STATS_MEMORY_USAGE => $stats['bytes'],
            CacheInterface::STATS_MEMORY_AVAILIABLE => $stats['limit_maxbytes'],
        ];
    }
}

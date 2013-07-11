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
 * @author Martin Rademacher <mano@radebatz.net>
 */
class MemcacheCache implements Cache
{
    const NAMESPACE_DELIMITER = '==';
    protected $memcache;
    protected $compress;

    /**
     * Create instance.
     *
     * @param array $config Optional config settings; default is an empty array.
     */
    public function __construct(array $config = array())
    {
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

        $entry = $this->memcache->get($this->namespaceId($id, $namespace));

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        return false !== $this->memcache->get($this->namespaceId($id, $namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        if ($this->contains($id, $namespace)) {
            $entry = $this->memcache->get($this->namespaceId($id, $namespace));

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

        return $this->memcache->set($this->namespaceId($id, $namespace), $entry, $this->compress, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return $this->memcache->delete($this->namespaceId($id, $namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            return $this->memcache->flush();
        } else {
            $namespace = implode(static::NAMESPACE_DELIMITER, (array) $namespace);
            // iterate over all entries and delete matching
            foreach ($this->memcache->getExtendedStats('items') as $host => $summary) {
                foreach ($summary['items'] as $slabId => $details) {
                    $slabItems = $this->memcache->getExtendedStats('cachedump', $slabId, $details['number']);
                    $keys = array_keys($slabItems[$host]);
                    foreach ($keys as $key) {
                        if (0 === strpos($key, $namespace)) {
                            $this->memcache->delete($key);
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
            Cache::STATS_SIZE => $stats['total_items'],
            Cache::STATS_HITS => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILIABLE => $stats['limit_maxbytes'],
        );
    }

}

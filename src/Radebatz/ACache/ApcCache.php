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
 * APC cache.
 *
 * Since APC does not remove expired entries automatically, the cache can be configured to trigger a
 * GC/cleanup run if any of the configured thresholds are reached.
 * Futhermore, there may be separate conditions configured to completely clear the APC cache (to clear fragmentation).
 *
 * GC runs are currently only done on save and only once in the lifetime of the cache instance. It is possible to force
 * more runs by calling gc() directly with the force option, though.
 *
 * Available GC options (all sizes are in byte):
 *  * gc_trigger_percent:  Available memory left in percent.
 *  * gc_trigger_size:     Available memory left in size.
 *  * gc_clear_percent:    Available memory left in percent.
 *  * gc_clear_size:       Available memory left in size.
 *  * gc_clear_f_percent:  Fragmentation in percent.
 *  * gc_f_block_size:     Max fragment blocksize in MBytes; default is 5. Available blocks larger will not be considered fragments.
 *  * gc_grace_period:     Grace period for expired entries to stay in memory; default is 300 seconds.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ApcCache extends AbstractPathKeyCache
{
    protected $options;
    protected $gcDone;

    /**
     * Create instance.
     *
     * @param array $options           Optional (GC) options.
     * @param int   $defaultTimeToLive Optional default time-to-live value.
     */
    public function __construct(array $options = array(), $defaultTimeToLive = 0)
    {
        parent::__construct(self::DEFAULT_NAMESPACE_DELIMITER, $defaultTimeToLive);

        $this->gcDone = false;
        $this->options = array_replace(
            array(
                'gc_trigger_percent' => null,
                'gc_trigger_size' => null,
                'gc_clear_percent' => null,
                'gc_clear_size' => null,
                'gc_clear_f_percent' => null,
                'gc_f_block_size' => 5,
                'gc_grace_period' => 300,
            ),
            $options
        );
    }

    /**
     * Run GC with the configured options.
     *
     * Calling this will do nothing unless this cache instances is configured to.
     *
     * @param  boolean      $force Optional flag to force a run.
     * @return boolean|null The result of the gc or <code>null</code> for a noop.
     */
    public function gc($force = false)
    {
        if ((!$this->gcDone || $force) && ($mem = apc_sma_info())) {
            $this->gcDone = true;
            // calculate what is left
            $size = $mem['num_seg'] * $mem['seg_size'];
            $avail = (double) $mem['avail_mem'];
            $avail_p = (int) sprintf('%d', $avail * 100 / $size);

            // check for clear first because then we do not have to do GC...
            if (null !== $this->options['gc_clear_percent'] && $this->options['gc_clear_percent'] > $avail_p) {
                return apc_clear_cache('user');
            }
            if (null !== $this->options['gc_clear_size'] && $this->options['gc_clear_size'] > $avail) {
                return apc_clear_cache('user');
            }
            if (null !== $this->options['gc_clear_f_percent']) {
                // first need to calculate current fragmentation
                $frag = 0;
                $nseg = $freeseg = $fragsize = $freetotal = 0;
                for ($ii=0; $ii<$mem['num_seg']; ++$ii) {
                    $ptr = 0;
                    foreach ($mem['block_lists'][$ii] as $block) {
                        if ($block['offset'] != $ptr) {
                            ++$nseg;
                        }
                        $ptr = $block['offset'] + $block['size'];
                        if ($block['size'] < ($this->options['gc_f_block_size'] * 1024 * 1024)) {
                            $fragsize += $block['size'];
                        }
                        $freetotal += $block['size'];
                    }
                    $freeseg += count($mem['block_lists'][$ii]);
                }

                if ($freeseg > 1) {
                    $frag = (int) sprintf('%d', ($fragsize / $freetotal) * 100 * 100);
                    if ($frag > $this->options['gc_clear_f_percent']) {
                        return apc_clear_cache('user');
                    }
                }
            }

            // GC
            if (null !== $this->options['gc_trigger_percent'] && $this->options['gc_trigger_percent'] > $avail_p
                || null !== $this->options['gc_trigger_size'] && $this->options['gc_trigger_size'] > $avail) {
                $now = time();
                $cacheInfo = apc_cache_info('user');
                foreach ($cacheInfo['cache_list'] as $entry) {
                    if ($entry['ttl'] && ($entry['creation_time'] + $entry['ttl'] + $this->options['gc_grace_period']) < $now) {
                        // expired and past grace period
                        apc_delete($entry['info']);
                    }
                }

                return true;
            }
        }

        return null;
    }

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
        $this->gc();

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
            CacheInterface::STATS_SIZE => count($cacheInfo['cache_list']),
            CacheInterface::STATS_HITS => $cacheInfo['num_hits'],
            CacheInterface::STATS_MISSES => $cacheInfo['num_misses'],
            CacheInterface::STATS_UPTIME => $cacheInfo['start_time'],
            CacheInterface::STATS_MEMORY_USAGE => $cacheInfo['mem_size'],
            CacheInterface::STATS_MEMORY_AVAILIABLE => $smaInfo['avail_mem'],
        );
    }

}

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
 * APC garbage collector.
 *
 * Available options (all sizes are in byte):
 *  * trigger_percent:  Available memory left in percent.
 *  * trigger_size:     Available memory left in size.
 *  * clear_percent:    Available memory left in percent.
 *  * clear_size:       Available memory left in size.
 *  * clear_f_percent:  Fragmentation in percent.
 *  * f_block_size:     Max fragment blocksize in MBytes; default is 5. Available blocks larger will not be considered fragments.
 *  * grace_period:     Grace period for expired entries to stay in memory; default is 300 seconds.
 *  * throttle:         Interval in seconds between executions unless run with <code>$force</code>; default is 10 seconds.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ApcGC
{
    protected $options;
    protected $lastRun;

    /**
     * Create instance.
     *
     * @param array $options Optional (GC) options.
     */
    public function __construct(array $options = array())
    {
        $this->options = array_replace(
            array(
                'trigger_percent' => null,
                'trigger_size' => null,
                'clear_percent' => null,
                'clear_size' => null,
                'clear_f_percent' => null,
                'f_block_size' => 5,
                'grace_period' => 300,
                'throttle' => 10,
            ),
            $options
        );

        $this->lastRun = 0;
    }

    /**
     * Run GC with the configured options.
     *
     * @param  boolean $force Optional flag to force a run.
     * @return boolean|null   The result of the gc or <code>null</code> for a noop.
     */
    public function run($force = false)
    {
        $options = $this->options;

        $now = time();
        if (($mem = apc_sma_info()) && ($force || ($now > $this->lastRun + $options['throttle']))) {
            $this->lastRun = $now;

            // calculate what is left
            $size = $mem['num_seg'] * $mem['seg_size'];
            $avail = (double) $mem['avail_mem'];
            $avail_p = (int) sprintf('%d', $avail * 100 / $size);

            // check for clear first because then we do not have to do GC...
            if (null !== $options['clear_percent'] && $options['clear_percent'] > $avail_p) {
                return apc_clear_cache('user');
            }
            if (null !== $options['clear_size'] && $options['clear_size'] > $avail) {
                return apc_clear_cache('user');
            }
            if (null !== $options['clear_f_percent']) {
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
                        if ($block['size'] < ($options['f_block_size'] * 1024 * 1024)) {
                            $fragsize += $block['size'];
                        }
                        $freetotal += $block['size'];
                    }
                    $freeseg += count($mem['block_lists'][$ii]);
                }

                if ($freeseg > 1) {
                    $frag = (int) sprintf('%d', ($fragsize / $freetotal) * 100 * 100);
                    if ($frag > $options['clear_f_percent']) {
                        return apc_clear_cache('user');
                    }
                }
            }

            // GC
            if (null !== $options['trigger_percent'] && $options['trigger_percent'] > $avail_p
                || null !== $options['trigger_size'] && $options['trigger_size'] > $avail) {
                $now = time();
                $cacheInfo = apc_cache_info('user');
                foreach ($cacheInfo['cache_list'] as $entry) {
                    if ($entry['ttl'] && ($entry['creation_time'] + $entry['ttl'] + $options['grace_period']) < $now) {
                        // expired and past grace period
                        apc_delete($entry['info']);
                    }
                }

                return true;
            }
        }

        return null;
    }

}

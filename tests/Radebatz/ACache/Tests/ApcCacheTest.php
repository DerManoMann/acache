<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Tests;

use Radebatz\ACache\ApcCache;

/**
 * ApcCache tests.
 */
class ApcCacheTest extends NamespaceCacheTest
{

    /**
     * Check if apc is available.
     */
    protected function hasApc()
    {
        return function_exists('apc_cache_info') && @apc_cache_info();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasApc()) {
            $this->markTestSkipped('Skipping Apc');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasApc()) {
            return null;
        }

        // flush apc for each run
        $cache = new ApcCache();
        $cache->flush();

        return array(
            array($cache)
        );
    }

    /**
     * Test gc.
     */
    public function testGC()
    {
        // enable gc and set default ttl to 1 second
        $cache = new ApcCache(array(
            'gc_trigger_percent' => 100, // trigger if less than 100% free
            'gc_grace_period' => 1, // 1 sec. grace period to speed things up
        ), 1);

        // add entry
        $cache->save('foo', 'bar');
        $stats = $cache->getStats();
        $this->assertEquals(1, $stats['size']);

        // wait to expire
        sleep(4);

        // get stats for later comparison
        $before = $cache->getStats();
        $this->assertEquals(1, $before['size']);

        // add another entry (no gc, though)
        $cache->save('ding', 'dong', 30);
        $stats = $cache->getStats();
        $this->assertEquals(2, $stats['size']);

        // force gc
        $cache->gc(true);

        // should be 1 - ding
        $after = $cache->getStats();
        $this->assertEquals(1, $after['size']);
        $this->assertFalse($cache->contains('foo'));
        $this->assertTrue($cache->contains('ding'));
    }

}

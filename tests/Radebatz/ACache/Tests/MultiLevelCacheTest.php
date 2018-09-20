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

use Radebatz\ACache\CacheInterface;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\MultiLevelCache;
use Radebatz\ACache\NullCache;

/**
 * MultiLevelCache tests.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class MultiLevelCacheTest extends CacheTest
{
    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        return [
            [new MultiLevelCache([new ArrayCache()], false, new TestLogger())],
            [new MultiLevelCache([new ArrayCache(), new ArrayCache()], false, new TestLogger())],
            [new MultiLevelCache([new ArrayCache()], true, new TestLogger())],
            [new MultiLevelCache([new ArrayCache(), new ArrayCache()], true, new TestLogger())],
            [new MultiLevelCache([new ArrayCache()], true, new TestLogger())],
            [new MultiLevelCache([new ArrayCache(), new ArrayCache()], false, new TestLogger())],
        ];
    }

    /**
     * Test default stuff.
     */
    public function testDefaults()
    {
        $cache = new MultiLevelCache([new ArrayCache(), new ArrayCache()], false, new TestLogger());
        $this->assertTrue($cache->available());

        $this->assertFalse($cache->contains('yin'));
        $this->assertNull($cache->fetch('yin'));

        $this->assertTrue($cache->save('yin', 'yang'));
        $this->assertTrue($cache->contains('yin'));
        $this->assertEquals('yang', $cache->fetch('yin'));

        $cache->flush();
        $this->assertFalse($cache->contains('foo'));
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(0, $stats[CacheInterface::STATS_SIZE]);
        }
    }

    /**
     * Test no bubbles.
     */
    public function testNoBubbles()
    {
        // no bubbles :{
        $cache = new MultiLevelCache([new ArrayCache(), new ArrayCache()], false, new TestLogger());
        $this->assertTrue($cache->available());

        $this->assertFalse($cache->isBubbleOnFetch());

        // save
        $this->assertTrue($cache->save('yin', 'yang'));
        // ensure we have populated all caches in the stack
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[CacheInterface::STATS_SIZE]);
        }

        // flush 1st level
        $stack = $cache->getStack();
        $stack[0]->flush();

        // fetch
        $this->assertEquals('yang', $cache->fetch('yin'));
        // check that fetch hasn't triggered any bubbles
        foreach ($cache->getStack() as $ii => $sc) {
            $stats = $sc->getStats();
            $this->assertEquals($ii, $stats[CacheInterface::STATS_SIZE]);
        }
    }

    /**
     * Test bubbles.
     */
    public function testBubbles()
    {
        // bubbles :}
        $cache = new MultiLevelCache([new ArrayCache(), new ArrayCache()], true, new TestLogger());
        $this->assertTrue($cache->available());

        $this->assertTrue($cache->isBubbleOnFetch());

        // save
        $this->assertTrue($cache->save('yin', 'yang'));
        // ensure we have populated all caches in the stack
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[CacheInterface::STATS_SIZE]);
        }

        // flush 1st level
        $stack = $cache->getStack();
        $stack[0]->flush();

        // fetch
        $this->assertEquals('yang', $cache->fetch('yin'));
        // check that fetch has triggered bubbles
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[CacheInterface::STATS_SIZE]);
        }
    }

    /**
     * Test unavailable.
     */
    public function testUnavailable()
    {
        $cache = new MultiLevelCache([new NullCache(false)], false, $logger = new TestLogger());
        $this->assertFalse($cache->available());
        $this->assertEquals(2, count($logger->lines));
        $this->assertContains('not available', $logger->lines[0]);
    }

    /**
     * Test unavailable with bubbling.
     */
    public function testUnavailableBubbling()
    {
        $cache = new MultiLevelCache([new NullCache(false), new ArrayCache()], true, new TestLogger());
        $this->assertTrue($cache->available());
        $this->assertEquals(1, count($cache->getStack()));
        $cache->save('foo', 'bar');
        $this->assertEquals('bar', $cache->fetch('foo'));
    }
}

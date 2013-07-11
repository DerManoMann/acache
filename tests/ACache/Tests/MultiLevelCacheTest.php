<?php
namespace ACache\Tests;

use ACache\Cache;
use ACache\ArrayCache;
use ACache\MultiLevelCache;

/**
 * MultiLevelCache tests
 */
class MultiLevelCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test default stuff.
     */
    public function testDefaults()
    {
        $cache = new MultiLevelCache(array(new ArrayCache(), new ArrayCache()));
        $this->assertFalse($cache->contains('ying'));
        $this->assertNull($cache->fetch('ying'));

        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertTrue($cache->contains('ying'));
        $this->assertEquals('yang', $cache->fetch('ying'));

        $cache->flush();
        $this->assertFalse($cache->contains('foo'));
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(0, $stats[Cache::STATS_SIZE]);
        }
    }

    /**
     * Test no bubbles.
     */
    public function testNoBubbles()
    {
        // no bubbles :{
        $cache = new MultiLevelCache(array(new ArrayCache(), new ArrayCache()), false);

        // save
        $this->assertTrue($cache->save('ying', 'yang'));
        // ensure we have populated all caches in the stack
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[Cache::STATS_SIZE]);
        }

        // flush 1st level
        $stack = $cache->getStack();
        $stack[0]->flush();

        // fetch
        $this->assertEquals('yang', $cache->fetch('ying'));
        // check that fetch hasn't triggered any bubbles
        foreach ($cache->getStack() as $ii => $sc) {
            $stats = $sc->getStats();
            $this->assertEquals($ii, $stats[Cache::STATS_SIZE]);
        }
    }


    /**
     * Test bubbles.
     */
    public function testBubbles()
    {
        // bubbles :}
        $cache = new MultiLevelCache(array(new ArrayCache(), new ArrayCache()), true);

        // save
        $this->assertTrue($cache->save('ying', 'yang'));
        // ensure we have populated all caches in the stack
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[Cache::STATS_SIZE]);
        }

        // flush 1st level
        $stack = $cache->getStack();
        $stack[0]->flush();

        // fetch
        $this->assertEquals('yang', $cache->fetch('ying'));
        // check that fetch has triggered bubbles
        foreach ($cache->getStack() as $sc) {
            $stats = $sc->getStats();
            $this->assertEquals(1, $stats[Cache::STATS_SIZE]);
        }
    }

}

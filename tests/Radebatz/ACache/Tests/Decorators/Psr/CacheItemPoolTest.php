<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Tests\Decorators;

use PHPUnit\Framework\TestCase;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

/**
 * Psr CacheItemPool tests.
 */
class CacheItemPoolTest extends TestCase
{
    /**
     * Get a cache pool.
     */
    protected function getCachePool()
    {
        return new CacheItemPool(new ArrayCache());
    }

    /**
     * Test get.
     */
    public function testGet()
    {
        $cachePool = $this->getCachePool();
        // pre-populate
        $cachePool->getCache()->save('ping', 'pong');

        // cache hit
        $cacheItem = $cachePool->getItem('ping');
        $this->assertNotNull($cacheItem);

        $this->assertTrue($cacheItem->isHit());

        $this->assertEquals('ping', $cacheItem->getKey());
        $this->assertEquals('pong', $cacheItem->get());
        $this->assertEquals('pong', $cacheItem->getValue());

        // cache miss
        $cacheItem = $cachePool->getItem('foo');
        $this->assertNotNull($cacheItem);

        $this->assertFalse($cacheItem->isHit());

        $this->assertEquals('foo', $cacheItem->getKey());
        $this->assertEquals(null, $cacheItem->get());
        $this->assertEquals(null, $cacheItem->getValue());
    }

    /**
     * Test save.
     */
    public function testSave()
    {
        $cachePool = $this->getCachePool();
        $cacheItem = $cachePool->getItem('ping');

        $this->assertFalse($cacheItem->isHit());

        $cacheItem->set('pong');

        // get() returns null as the item is not in the cache
        $this->assertEquals('ping', $cacheItem->getKey());
        $this->assertEquals(null, $cacheItem->get());
        $this->assertEquals('pong', $cacheItem->getValue());

        // save in pool
        $cachePool->save($cacheItem);

        // is it in the actual cache?
        $this->assertTrue($cachePool->getCache()->contains('ping'));

        $this->assertTrue($cacheItem->isHit());

        // now get() returns the value
        $this->assertEquals('ping', $cacheItem->getKey());
        $this->assertEquals('pong', $cacheItem->get());
        $this->assertEquals('pong', $cacheItem->getValue());
    }

    /**
     * Test clear.
     */
    public function testClear()
    {
        $cachePool = $this->getCachePool();
        $cache = $cachePool->getCache();
        // pre-populate with some data
        $cache->save('ping', 'pong');

        $cacheItem = $cachePool->getItem('ping');
        $this->assertTrue($cacheItem->isHit());

        // clear pool
        $cachePool->clear();

        // cleared from underlying cache
        $this->assertFalse($cache->contains('ping'));
        // cache item should reflect changed state
        $this->assertFalse($cacheItem->isHit());
    }

    /**
     * Test delete.
     */
    public function testDelete()
    {
        $cachePool = $this->getCachePool();
        $cache = $cachePool->getCache();
        // pre-populate with some data
        $cache->save('ping', 'pong');

        // all good?
        $cacheItem = $cachePool->getItem('ping');
        $this->assertTrue($cacheItem->isHit());

        $cachePool->deleteItems(['ping']);

        // cleared from underlying cache
        $this->assertFalse($cache->contains('ping'));
        // cache item should reflect changed state
        $this->assertFalse($cacheItem->isHit());
    }

    /**
     * Test saveDeferred.
     */
    public function testSaveDeferred()
    {
        $cachePool = $this->getCachePool();
        $cache = $cachePool->getCache();

        $cacheItem = $cachePool->getItem('foo');
        $cacheItem->set('bar');
        $cachePool->saveDeferred($cacheItem);

        // same behaviour as hit (pulling fresh from pool)
        $this->assertEquals('bar', $cachePool->getItem('foo')->get());
        $this->assertEquals('bar', $cacheItem->get());
    }

    /**
     * Test commit.
     */
    public function testPoolCommit()
    {
        $cachePool = $this->getCachePool();
        $cache = $cachePool->getCache();

        $cacheItem = $cachePool->getItem('foo');
        $cacheItem->set('bar');
        $cachePool->saveDeferred($cacheItem);

        $cachePool->commit();

        $this->assertTrue($cache->contains('foo'));
        $this->assertEquals('bar', $cacheItem->get());
        $this->assertEquals('bar', $cachePool->getItem('foo')->get());
    }

    /**
     * Test invalid key.
     *
     * @expectedException Radebatz\ACache\Decorators\Psr\InvalidArgumentException
     */
    public function testInvalidKey()
    {
        $cachePool = $this->getCachePool();
        $cachePool->getItem('(foo');
    }
}

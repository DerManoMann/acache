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
use Radebatz\ACache\NamespaceCache;

/**
 * NamespaceCache tests
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class NamespaceCacheTest extends CacheTest
{

    /**
     * Provide cache instances for testing.
     */
    public function cacheProvider()
    {
        return array(
            array(new ArrayCache()),
            array(new NamespaceCache(new ArrayCache(), 'other')),
        );
    }

    /**
     * Test namespace.
     *
     * @dataProvider cacheProvider
     */
    public function testNamespace(CacheInterface $cache)
    {
        $this->doTestNamespace($cache);
        $this->doTestNamespace(new NamespaceCache($cache, 'super'));
    }

    /**
     * Do namespace tests.
     */
    protected function doTestNamespace(CacheInterface $decoratedCache)
    {
        // ensure we are clean
        $decoratedCache->flush();
        if ($decoratedCache instanceof NamespaceCache) {
            $decoratedCache->getCache()->flush();
        }
        $cache = new NamespaceCache($decoratedCache, 'foo');

        $this->assertFalse($cache->contains('yin'));
        $this->assertNull($cache->fetch('yin'));

        $this->assertTrue($cache->save('yin', 'yang'));
        $this->assertTrue($cache->contains('yin'));
        $this->assertEquals('yang', $cache->fetch('yin'));
        // check decorated cache to make sure there is a namespace in the name...
        $this->assertFalse($decoratedCache->contains('yin'));

        $this->assertTrue($cache->delete('yin'));
        $this->assertFalse($cache->contains('yin'));
        $this->assertNull($cache->fetch('yin'));

        $stats = $decoratedCache->getStats();
        $this->assertEquals(0, $stats[CacheInterface::STATS_SIZE]);

        $this->assertTrue($cache->save('foo', 'bar'));
        $stats = $decoratedCache->getStats();
        $this->assertEquals(1, $stats[CacheInterface::STATS_SIZE]);

        $cache->flush();
        if ($stats = $decoratedCache->getStats()) {
            $this->assertFalse($cache->contains('foo'));
            $this->assertEquals(0, $stats[CacheInterface::STATS_SIZE]);
        }
    }

    /**
     * Test empty namespace.
     *
     * @dataProvider cacheProvider
     */
    public function testEmptyNamespace(CacheInterface $cache)
    {
        // regular
        $this->doTestEmptyNamespace($cache);
        $this->doTestEmptyNamespace(new NamespaceCache($cache, 'super'));
    }

    /**
     * Do empty namespace tests.
     */
    protected function doTestEmptyNamespace(CacheInterface $decoratedCache)
    {
        // ensure we are clean
        $decoratedCache->flush();
        if ($decoratedCache instanceof NamespaceCache) {
            $decoratedCache->getCache()->flush();
        }
        $cache = new NamespaceCache($decoratedCache);

        $this->assertFalse($cache->contains('yin'));
        $this->assertNull($cache->fetch('yin'));

        $this->assertTrue($cache->save('yin', 'yang'));
        $this->assertTrue($cache->contains('yin'));
        $this->assertEquals('yang', $cache->fetch('yin'));
        // check decorated cache as that should be the same
        $this->assertTrue($decoratedCache->contains('yin'));

        $cache->flush();
        if ($stats = $decoratedCache->getStats()) {
            $this->assertFalse($cache->contains('foo'));
            $this->assertEquals(0, $stats[CacheInterface::STATS_SIZE]);
        }
    }

}

<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ACache\Tests;

use ACache\Cache;
use ACache\ArrayCache;
use ACache\ApcCache;
use ACache\FilesystemCache;
use ACache\NamespaceCache;

/**
 * NamespaceCache tests
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class NamespaceCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Provide cache instances for testing.
     */
    public function cacheProvider()
    {
        return array(
            array(new ArrayCache()),
            array(new ApcCache()),
            array(new FilesystemCache(__DIR__.'/cache')),
        );
    }

    /**
     * Test namespace.
     *
     * @dataProvider cacheProvider
     */
    public function testNamespace(Cache $cache)
    {
        // regular
        $this->doTestNamespace($cache);

        // nested
        $this->doTestNamespace(new NamespaceCache($cache, 'super'));
    }

    /**
     * Do namespace tests.
     */
    protected function doTestNamespace(Cache $decoratedCache)
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
        $this->assertEquals(0, $stats[Cache::STATS_SIZE]);

        $this->assertTrue($cache->save('foo', 'bar'));
        $stats = $decoratedCache->getStats();
        $this->assertEquals(1, $stats[Cache::STATS_SIZE]);

        $cache->flush();
        $stats = $decoratedCache->getStats();
        $this->assertFalse($cache->contains('foo'));
        $this->assertEquals(0, $stats[Cache::STATS_SIZE]);
    }

    /**
     * Test empty namespace.
     *
     * @dataProvider cacheProvider
     */
    public function testEmptyNamespace(Cache $cache)
    {
        // regular
        $this->doTestEmptyNamespace($cache);

        // nested
        $this->doTestEmptyNamespace(new NamespaceCache($cache, 'super'));
    }

    /**
     * Do empty namespace tests.
     */
    protected function doTestEmptyNamespace(Cache $decoratedCache)
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
        $stats = $decoratedCache->getStats();
        $this->assertFalse($cache->contains('foo'));
        $this->assertEquals(0, $stats[Cache::STATS_SIZE]);
    }

}

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
     * Test namespace.
     */
    public function testNamespace()
    {
        // regular
        $this->doTestNamespace(new ArrayCache());
        $this->doTestNamespace(new ApcCache());
        $this->doTestNamespace(new FilesystemCache(__DIR__.'/cache/1'));

        // nested
        $this->doTestNamespace(new NamespaceCache(new ArrayCache(), 'super'));
        $this->doTestNamespace(new NamespaceCache(new ApcCache(), 'super'));
        $this->doTestNamespace(new NamespaceCache(new FilesystemCache(__DIR__.'/cache/2'), 'super'));
    }

    /**
     * Do namespace tests.
     */
    protected function doTestNamespace(Cache $decoratedCache)
    {
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
     */
    public function testEmptyNamespace()
    {
        // regular
        $this->doTestEmptyNamespace(new ArrayCache());
        $this->doTestEmptyNamespace(new ApcCache());
        $this->doTestEmptyNamespace(new FilesystemCache(__DIR__.'/cache/3'));

        // nested
        $this->doTestEmptyNamespace(new NamespaceCache(new ArrayCache(), 'super'));
        $this->doTestEmptyNamespace(new NamespaceCache(new ApcCache(), 'super'));
        $this->doTestEmptyNamespace(new NamespaceCache(new FilesystemCache(__DIR__.'/cache/4'), 'super'));
    }

    /**
     * Do empty namespace tests.
     */
    protected function doTestEmptyNamespace(Cache $decoratedCache)
    {
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

<?php
namespace ACache\Tests;

use ACache\Cache;
use ACache\ArrayCache;
use ACache\ApcCache;
use ACache\FilesystemCache;
use ACache\NamespaceCache;

/**
 * NamespaceCache tests
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
        $this->assertFalse($cache->contains('ying'));
        $this->assertNull($cache->fetch('ying'));

        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertTrue($cache->contains('ying'));
        $this->assertEquals('yang', $cache->fetch('ying'));
        // check decorated cache to make sure there is a namespace in the name...
        $this->assertFalse($decoratedCache->contains('ying'));

        $this->assertTrue($cache->delete('ying'));
        $this->assertFalse($cache->contains('ying'));
        $this->assertNull($cache->fetch('ying'));

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
        $this->assertFalse($cache->contains('ying'));
        $this->assertNull($cache->fetch('ying'));

        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertTrue($cache->contains('ying'));
        $this->assertEquals('yang', $cache->fetch('ying'));
        // check decorated cache as that should be the same
        $this->assertTrue($decoratedCache->contains('ying'));

        $cache->flush();
        $stats = $decoratedCache->getStats();
        $this->assertFalse($cache->contains('foo'));
        $this->assertEquals(0, $stats[Cache::STATS_SIZE]);
    }

}

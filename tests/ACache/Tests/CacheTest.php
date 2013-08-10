<?php
namespace ACache\Tests;

use ReflectionClass;
use ACache\Cache;

/**
 * Cache test base class.
 *
 * Tests that each cache must pass.
 */
abstract class CacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test contains.
     *
     * @dataProvider cacheProvider
     */
    public function testContains(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertTrue($cache->contains('ying'));
        $this->assertFalse($cache->contains('bar'));
    }

    /**
     * Get protected/private property.
     *
     * @param  mixed  $obj  The opject.
     * @param  string $name The property name.
     * @return mixed  The property or <code>null</code>.
     */
    protected function getProperty($obj, $name)
    {
        $rc = new ReflectionClass($obj);
        if ($property = $rc->getProperty($name)) {
            $property->setAccessible(true);

            return $property->getValue($obj);
        }

        return null;
    }

    /**
     * Test fetch.
     *
     * @dataProvider cacheProvider
     */
    public function testFetch(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertEquals('yang', $cache->fetch('ying'));
        $this->assertEquals(null, $cache->fetch('bar'));
    }

    /**
     * Test fetch invalid.
     *
     * @dataProvider cacheProvider
     */
    public function testFetchInvalid(Cache $cache)
    {
        $this->assertNull($cache->fetch('foobar'));
    }

    /**
     * Test save.
     *
     * @dataProvider cacheProvider
     */
    public function testSave(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertEquals('yang', $cache->fetch('ying'));
        $this->assertTrue($cache->save('ying', 'ding'));
        $this->assertEquals('ding', $cache->fetch('ying'));
    }

    /**
     * Test time to live.
     *
     * @dataProvider cacheProvider
     */
    public function testTimeToLive(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertEquals(0, $cache->getTimeToLive('foo'));
        $this->assertFalse($cache->getTimeToLive('bert'));

        $this->assertTrue($cache->save('dough', 'nut', null, 20));
        $this->assertGreaterThan(15, $cache->getTimeToLive('dough'));

        $this->assertTrue($cache->save('dough', 'nut'));
        $this->assertEquals(0, $cache->getTimeToLive('dough'));
    }

    /**
     * Test delete.
     *
     * @dataProvider cacheProvider
     */
    public function testDelete(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertEquals('bar', $cache->fetch('foo'));
        $this->assertTrue($cache->delete('foo'));
        $this->assertFalse($cache->contains('foo'));
        $this->assertNull($cache->fetch('foo'));
    }

    /**
     * Test flush.
     *
     * @dataProvider cacheProvider
     */
    public function testFlush(Cache $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertEquals('bar', $cache->fetch('foo'));
        $this->assertTrue($cache->flush());
        $this->assertFalse($cache->contains('foo'));
        $this->assertNull($cache->fetch('foo'));
    }

    /**
     * Test stats.
     *
     * @dataProvider cacheProvider
     */
    public function testStats(Cache $cache)
    {
        $this->assertTrue(is_array($stats = $cache->getStats()) || null === $stats);
    }

}

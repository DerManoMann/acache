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

use PHPUnit\Framework\TestCase;
use Radebatz\ACache\CacheInterface;

/**
 * Cache test base class.
 *
 * Tests that each cache must pass.
 */
abstract class CacheTest extends TestCase
{
    /**
     * Test contains.
     *
     * @dataProvider cacheProvider
     */
    public function testContains(CacheInterface $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertTrue($cache->contains('ying'));
        $this->assertFalse($cache->contains('bar'));
    }

    /**
     * Get protected/private property.
     *
     * @param mixed  $obj  the opject
     * @param string $name the property name
     *
     * @return mixed the property or <code>null</code>
     */
    protected function getProperty($obj, $name)
    {
        $rc = new \ReflectionClass($obj);
        if ($property = $rc->getProperty($name)) {
            $property->setAccessible(true);

            return $property->getValue($obj);
        }

        return null;
    }

    /**
     * Get an override value if it exists.
     *
     * @param string $name    the override name
     * @param mixed  $default optional default
     *
     * @return mixed the value or default
     */
    protected function getOverride($name, $default = null)
    {
        return defined($name) ? constant($name) : $default;
    }

    /**
     * Test fetch.
     *
     * @dataProvider cacheProvider
     */
    public function testFetch(CacheInterface $cache)
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
    public function testFetchInvalid(CacheInterface $cache)
    {
        $this->assertNull($cache->fetch('foobar'));
    }

    /**
     * Test save.
     *
     * @dataProvider cacheProvider
     */
    public function testSave(CacheInterface $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->save('ying', 'yang'));
        $this->assertEquals('yang', $cache->fetch('ying'));
        $this->assertTrue($cache->save('ying', 'ding'));
        $this->assertEquals('ding', $cache->fetch('ying'));
    }

    /**
     * Test arrays.
     *
     * @dataProvider cacheProvider
     */
    public function testArray(CacheInterface $cache)
    {
        $this->assertTrue($cache->save('arr', ['arr']));
        $this->assertEquals(['arr'], $cache->fetch('arr'));
    }

    /**
     * Test objects.
     *
     * @dataProvider cacheProvider
     */
    public function testObjecets(CacheInterface $cache)
    {
        $foo = json_decode('{"foo":"bar"}');
        $this->assertTrue($cache->save('foo', $foo));
        $this->assertEquals($foo, $cache->fetch('foo'));
    }

    /**
     * Test time to live.
     *
     * @dataProvider cacheProvider
     */
    public function testTimeToLive(CacheInterface $cache)
    {
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertEquals(0, $cache->getTimeToLive('foo'));
        $this->assertFalse($cache->getTimeToLive('bert'));

        $this->assertTrue($cache->save('dough', 'nut', 20, null));
        $this->assertGreaterThan(15, $cache->getTimeToLive('dough'));

        $this->assertTrue($cache->save('dough', 'nut'));
        $this->assertEquals(0, $cache->getTimeToLive('dough'));

        $this->assertEquals(0, $cache->getDefaultTimeToLive());
    }

    /**
     * Test delete.
     *
     * @dataProvider cacheProvider
     */
    public function testDelete(CacheInterface $cache)
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
    public function testFlush(CacheInterface $cache)
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
    public function testStats(CacheInterface $cache)
    {
        $this->assertTrue(is_array($stats = $cache->getStats()) || null === $stats);
    }
}

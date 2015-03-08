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

use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\DoctrineCache;

/**
 * DoctrineCache tests.
 */
class DoctrineCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if doctrine cache is available.
     */
    protected function hasDoctrineCache()
    {
        return class_exists('Doctrine\Common\Cache\Version');
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasDoctrineCache()) {
            $this->markTestSkipped('Skipping DoctrineCache');
        }
    }

    /**
     * Simple testing.
     */
    public function testSimple()
    {
        $cache = new DoctrineCache(new ArrayCache());
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertTrue($cache->contains('foo'));
        $this->assertEquals('bar', $cache->fetch('foo'));
        $this->assertTrue($cache->delete('foo'));
        $this->assertFalse($cache->contains('foo'));
    }

    /**
     * Test objects.
     */
    public function testObjecets()
    {
        $cache = new DoctrineCache(new ArrayCache());
        $foo = json_decode('{"foo":"bar"}');
        $this->assertTrue($cache->save('foo', $foo));
        $this->assertEquals($foo, $cache->fetch('foo'));
    }
}

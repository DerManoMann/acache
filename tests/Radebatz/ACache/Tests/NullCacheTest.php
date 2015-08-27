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

use Radebatz\ACache\NullCache;

/**
 * NullCache tests.
 */
class NullCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Simple testing.
     */
    public function testSimple()
    {
        $cache = new NullCache();
        $this->assertTrue($cache->save('foo', 'bar'));
        $this->assertFalse($cache->contains('foo'));
        $this->assertTrue($cache->delete('foo'));
        $this->assertFalse($cache->contains('foo'));
    }
}

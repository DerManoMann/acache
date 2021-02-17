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
use Radebatz\ACache\ApcCache;
use Radebatz\ACache\ApcGC;

/**
 * ApcGC tests.
 */
class ApcGCTest extends TestCase
{
    /**
     * Check if apc is available.
     */
    protected function hasApc()
    {
        return function_exists('apc_cache_info') && @apc_cache_info();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!$this->hasApc()) {
            $this->markTestSkipped('Skipping Apc');
        }
    }

    /**
     * Test run.
     */
    public function testRun()
    {
        $gc = new ApcGC([
            'trigger_percent' => 100, // trigger if less than 100% free
            'grace_period' => 1, // 1 sec. grace period to speed things up
        ]);

        // use gc and set default ttl to 1 second
        $cache = new ApcCache(1, $gc);

        // add entry
        $cache->save('foo', 'bar');
        $stats = $cache->getStats();
        $this->assertEquals(1, $stats['size']);

        // wait to expire
        sleep(4);

        // get stats for later comparison
        $before = $cache->getStats();
        $this->assertEquals(1, $before['size']);

        // add another entry (no gc, though)
        $cache->save('ding', 'dong', 30);
        $stats = $cache->getStats();
        $this->assertEquals(2, $stats['size']);

        // force gc
        $gc->run(true);

        // should be 1 - ding
        $after = $cache->getStats();
        $this->assertEquals(1, $after['size']);
        $this->assertFalse($cache->contains('foo'));
        $this->assertTrue($cache->contains('ding'));
    }

    /**
     * Test throttle.
     */
    public function testThrottle()
    {
        $gc = new ApcGC([
            'trigger_percent' => 100,
            'grace_period' => 1,
            'throttle' => 3,
        ]);
        $cache = new ApcCache(1, $gc);
        // will trigger the initial run
        $cache->save('foo', 'bar');

        // throttled
        $this->assertNull($gc->run());
        // force
        $this->assertTrue($gc->run(true));
        // throttled
        $this->assertNull($gc->run());

        // wait for throttle
        sleep(4);

        $this->assertTrue($gc->run());
    }
}

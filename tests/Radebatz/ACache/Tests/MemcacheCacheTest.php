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

use Radebatz\ACache\MemcacheCache;

/**
 * MemcacheCache tests.
 */
class MemcacheCacheTest extends NamespaceCacheTest
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $cache = new MemcacheCache();
        if (!$cache->available()) {
            $this->markTestSkipped('Skipping Memcache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $cache = new MemcacheCache();
        if (!$cache->available()) {
            return;
        }

        return [
            [$cache],
        ];
    }
}

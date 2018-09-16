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

use Radebatz\ACache\XCacheCache;

/**
 * XCacheCache tests.
 */
class XCacheCacheTest extends NamespaceCacheTest
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $cache = new XCacheCache();
        // xcache will not work in CL mode
        if (!$cache->available() || !isset($_SERVER)) {
            $this->markTestSkipped('Skipping XCache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $cache = new XCacheCache();
        if (!$cache->available() || !isset($_SERVER)) {
            return;
        }

        return [
            [$cache],
        ];
    }
}

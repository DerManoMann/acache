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

use Radebatz\ACache\ApcCache;

/**
 * ApcCache tests.
 */
class ApcCacheTest extends NamespaceCacheTest
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $cache = new ApcCache();
        if (!$cache->available()) {
            $this->markTestSkipped('Skipping Apc');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $cache = new ApcCache();

        if (!$cache->available()) {
            return;
        }

        // flush apc for each run
        $cache->flush();

        return [
            [$cache],
        ];
    }
}

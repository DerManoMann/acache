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

use Radebatz\ACache\WincacheCache;

/**
 * WincacheCache tests.
 */
class WincacheCacheTest extends NamespaceCacheTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $cache = new WincacheCache();
        if (!$cache->available()) {
            $this->markTestSkipped('Skipping Wincache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $cache = new WincacheCache();
        if (!$cache->available()) {
            return;
        }

        return [
            [$cache],
        ];
    }
}

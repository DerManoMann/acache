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
     * Check if apc is available.
     */
    protected function hasApc()
    {
        return function_exists('apc_cache_info') && @apc_cache_info();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasApc()) {
            $this->markTestSkipped('Skipping Apc');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasApc()) {
            return null;
        }

        // flush apc for each run
        $cache = new ApcCache();
        $cache->flush();

        return array(
            array($cache)
        );
    }

}

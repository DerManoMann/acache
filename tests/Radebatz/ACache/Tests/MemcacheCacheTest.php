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
     * Check if memcache is available.
     */
    protected function hasMemcache()
    {
        return class_exists('Memcache');
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasMemcache()) {
            $this->markTestSkipped('Skipping Memcache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasMemcache()) {
            return null;
        }

        return array(
            array(new MemcacheCache())
        );
    }

}

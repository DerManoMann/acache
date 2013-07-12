<?php
namespace ACache\Tests;

use ACache\MemcacheCache;

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
     * Cache provider.
     */
    public function cacheProvider()
    {
        return array(
            array(new MemcacheCache())
        );
    }

}

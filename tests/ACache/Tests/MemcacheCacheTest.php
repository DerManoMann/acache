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

<?php
namespace Radebatz\ACache\Tests;

use Radebatz\ACache\XCacheCache;

/**
 * XCacheCache tests.
 */
class XCacheCacheTest extends NamespaceCacheTest
{

    /**
     * Check if xcache is available.
     */
    protected function hasXCache()
    {
        // xcache will not work in CL mode
        return function_exists('xcache_info') && isset($_SERVER);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasXCache()) {
            $this->markTestSkipped('Skipping XCache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasXCache()) {
            return null;
        }

        return array(
            array(new XCacheCache())
        );
    }

}

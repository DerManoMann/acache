<?php
namespace ACache\Tests;

use ACache\ApcCache;

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
        return function_exists('apc_cache_info') && apc_cache_info();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasApc()) {
            $this->markTestSkipped('Skipping APC');
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
<?php
namespace ACache\Tests;

use ACache\WincacheCache;

/**
 * WincacheCache tests.
 */
class WincacheCacheTest extends NamespaceCacheTest
{

    /**
     * Check if wincache is available.
     */
    protected function hasWincache()
    {
        return function_exists('wincache_ucache_exists');
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasWincache()) {
            $this->markTestSkipped('Skipping Wincache');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasWincache()) {
            return null;
        }

        return array(
            array(new WincacheCache())
        );
    }

}

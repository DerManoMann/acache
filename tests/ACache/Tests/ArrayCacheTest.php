<?php
namespace ACache\Tests;

use ACache\ArrayCache;

/**
 * ArrayCache tests.
 */
class ArrayCacheTest extends NamespaceCacheTest
{

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        return array(
            array(new ArrayCache())
        );
    }

}

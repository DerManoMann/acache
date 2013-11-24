<?php
namespace Radebatz\ACache\Tests;

use Radebatz\ACache\ArrayCache;

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

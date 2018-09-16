<?php

namespace Radebatz\ACache\Tests\Decorators\Psr;

use Cache\IntegrationTests\CachePoolTest;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

/**
 * Additional Psr integration tests.
 */
class IntegrationCachePoolTest extends CachePoolTest
{
    /*
     * {@inheritdoc}
     */
    public function createCachePool()
    {
        return new CacheItemPool(new ArrayCache([], true));
    }
}

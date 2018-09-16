<?php

namespace Radebatz\ACache\Tests\Decorators\Psr;

use Cache\IntegrationTests\TaggableCachePoolTest;
use Cache\Taggable\TaggablePSR6PoolAdapter;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

/**
 * Additional Psr integration tests.
 */
class IntegrationTaggableCachePoolTest extends TaggableCachePoolTest
{
    /*
     * {@inheritdoc}
     */
    public function createCachePool()
    {
        return TaggablePSR6PoolAdapter::makeTaggable(new CacheItemPool(new ArrayCache([], true)));
    }
}

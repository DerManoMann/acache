<?php

namespace Radebatz\ACache\Tests\Decorators;

use Cache\IntegrationTests\CachePoolTest;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

class PoolIntegrationTest extends CachePoolTest
{
    public function createCachePool()
    {
        return new CacheItemPool(new ArrayCache());
    }
}

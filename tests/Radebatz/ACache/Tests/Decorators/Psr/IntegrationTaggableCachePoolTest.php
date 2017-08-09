<?php

namespace Radebatz\ACache\Tests\Decorators\Psr;

use Cache\IntegrationTests\TaggableCachePoolTest;
use Cache\Taggable\TaggablePSR6PoolAdapter;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

if (false && version_compare(phpversion(), '5.4.0', 'ge') && class_exists('\Cache\IntegrationTests\TaggableCachePoolTest')) {

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
        return TaggablePSR6PoolAdapter::makeTaggable(new CacheItemPool(new ArrayCache(array(), true)));
    }
}

} else {

/**
 * Dummy Psr integration tests.
 */
class IntegrationTaggableCachePoolTest extends \PHPUnit_Framework_TestCase
{
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}

}

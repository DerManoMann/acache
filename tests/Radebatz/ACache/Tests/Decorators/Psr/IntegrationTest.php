<?php

namespace Radebatz\ACache\Tests\Decorators\Psr;

use Cache\IntegrationTests\CachePoolTest;
use Radebatz\ACache\ApcCache;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

if (version_compare(phpversion(), '5.4.0', 'ge') && class_exists('\Cache\IntegrationTests\CachePoolTest')) {

/**
 * Additional Psr integration tests.
 */
class IntegrationTest extends CachePoolTest
{
    /*
     * {@inheritdoc}
     */
    public function createCachePool()
    {
        return new CacheItemPool(new ApcCache());
        return new CacheItemPool(new ArrayCache());
    }
}

} else {

/**
 * Dummy Psr integration tests.
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}

}

<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Tests;

use Redis;
use Radebatz\ACache\RedisCache;

/**
 * RedisCache tests.
 */
class RedisCacheTest extends NamespaceCacheTest
{
    /**
     * Check if redis is available.
     */
    protected function hasRedis()
    {
        return class_exists('Redis');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!$this->hasRedis()) {
            $this->markTestSkipped('Skipping Redis');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasRedis()) {
            return;
        }

        $redis = new Redis();
        $redis->connect('localhost');

        return [
            [new RedisCache($redis)],
        ];
    }
}

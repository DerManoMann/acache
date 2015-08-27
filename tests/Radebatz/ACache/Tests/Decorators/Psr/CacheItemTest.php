<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Tests\Decorators;

use DateTime;
//use DateTimeImmutable;
use DateInterval;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItem;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;
use Radebatz\ACache\Decorators\Psr\InvalidArgumentException;

/**
 * Psr CacheItem tests.
 */
class CacheItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get a cache item.
     */
    protected function getCacheItem($key, $value, $hit = false, $ttl = null) {
        $cache = new ArrayCache();

        if ($hit) {
            $cache->save($key, $value);
        }

        return new CacheItem($key, $value, new CacheItemPool($cache), $ttl);
    }

    /**
     * Test miss.
     */
    public function testMiss()
    {
        $cacheItem = $this->getCacheItem('ping', 'ping');
        $this->assertEquals('ping', $cacheItem->getKey());
        $this->assertNull($cacheItem->get());

        $this->assertFalse($cacheItem->isHit());
    }

    /**
     * Test hit.
     */
    public function testHit()
    {
        $cacheItem = $this->getCacheItem('ping', 'pong', true);
        $this->assertEquals('ping', $cacheItem->getKey());
        $this->assertEquals('pong', $cacheItem->get());

        $this->assertTrue($cacheItem->isHit());
    }

    /**
     * Test invalid expiresAt.
     *
     * @expectedException Radebatz\ACache\Decorators\Psr\InvalidArgumentException
     */
    public function testInvalidExpiresAt()
    {
        $cacheItem = $this->getCacheItem('ping', 'pong');
        $cacheItem->expiresAt('yo');
    }

    /**
     * Test invalid key.
     *
     * @expectedException Radebatz\ACache\Decorators\Psr\InvalidArgumentException
     */
    public function testInvalidKey()
    {
        $cacheItem = $this->getCacheItem('{ping', 'pong');
    }

    /**
     * Test set.
     */
    public function testSet()
    {
        $cacheItem = $this->getCacheItem('ping', 'pong');
        $this->assertSame($cacheItem, $cacheItem->set('foo'));
        $this->assertFalse($cacheItem->isHit());

        $cacheItem = $this->getCacheItem('ping', 'pong', true);
        $this->assertSame($cacheItem, $cacheItem->set('foo'));
        $this->assertEquals('foo', $cacheItem->get());
        $this->assertTrue($cacheItem->isHit());
    }

    /**
     * Expires at date data provider.
     */
    public function expiresAtDateDataProvider()
    {
        return array(
            // date, expectedExpiresAt
            'oneDay' => array(new DateTime('1 day'), new DateTime('1 day')),
            //'oneDayImmutable' => array(new DateTimeImmutable('1 day'), new DateTimeImmutable('1 day')),
            'zero' => array(null, null),
        );
    }

    /**
     * @dataProvider expiresAtDateDataProvider
     */
    public function testExpiresAt($expiration, $expectedExpiresAt)
    {
        $cacheItem = $this->getCacheItem('ping', 'pong');

        $this->assertSame($cacheItem, $cacheItem->expiresAt($expiration));
        $this->assertEquals($expectedExpiresAt, $cacheItem->getExpiresAt());
    }

    /**
     * Expires after time data provider.
     */
    public function expiresAfterTimeDataProvider()
    {
        return array(
            // date, ttl (here we use ttl as the expiredAt instance is created as part of the expiresAfter call)
            'five' => array(5, '5 seconds'),
            'oneDay' => array(new DateInterval('P1D'), '1 day'),
        );
    }

    /**
     * @dataProvider expiresAfterTimeDataProvider
     */
    public function testExpiresAfter($time, $ttl)
    {
        $cacheItem = $this->getCacheItem('ping', 'pong');

        $expectedExpiresAt = new DateTime($ttl);
        $this->assertSame($cacheItem, $cacheItem->expiresAfter($time));
        $this->assertEquals($expectedExpiresAt, $cacheItem->getExpiresAt());
    }
}

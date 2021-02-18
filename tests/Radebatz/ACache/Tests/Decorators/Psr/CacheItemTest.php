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

use PHPUnit\Framework\TestCase;
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItem;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;
use Radebatz\ACache\Decorators\Psr\InvalidArgumentException;

/**
 * Psr CacheItem tests.
 */
class CacheItemTest extends TestCase
{
    /**
     * Get a cache item.
     */
    protected function getCacheItem($key, $value, $hit = false, $ttl = null)
    {
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
     */
    public function testInvalidExpiresAt()
    {
        $this->expectException(InvalidArgumentException::class);

        $cacheItem = $this->getCacheItem('ping', 'pong');
        $cacheItem->expiresAt('yo');
    }

    /**
     * Test invalid key.
     */
    public function testInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getCacheItem('{ping', 'pong');
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
        return [
            // date, expectedExpiresAt
            'oneDay' => [new \DateTime('1 day'), new \DateTime('1 day')],
            //'oneDayImmutable' => array(new DateTimeImmutable('1 day'), new DateTimeImmutable('1 day')),
            'zero' => [null, null],
        ];
    }

    /**
     * @dataProvider expiresAtDateDataProvider
     */
    public function testExpiresAt($expiration, $expectedExpiresAt)
    {
        $cacheItem = $this->getCacheItem('ping', 'pong');

        $this->assertSame($cacheItem, $cacheItem->expiresAt($expiration));
        if (!$expectedExpiresAt) {
            $this->assertNull($cacheItem->getExpiresAt());
        } else {
            $this->assertEquals($expectedExpiresAt->format(\DateTime::RFC822), $cacheItem->getExpiresAt()->format(\DateTime::RFC822));
        }
    }

    /**
     * Expires after time data provider.
     */
    public function expiresAfterTimeDataProvider()
    {
        return [
            'five' => [5, '5 seconds'],
            'oneDay' => [new \DateInterval('P1D'), '1 day'],
        ];
    }

    /**
     * @dataProvider expiresAfterTimeDataProvider
     */
    public function testExpiresAfter($ttl, $interval)
    {
        $cacheItem = $this->getCacheItem('ping', 'pong', false, $ttl);

        $expectedExpiresAt = null;
        if ($ttl instanceof \DateInterval) {
            $expectedExpiresAt = (new \DateTime())->add($ttl);
        } else {
            $expectedExpiresAt = new \DateTime('@' . (time() + $ttl));
        }

        $this->assertSame($cacheItem, $cacheItem->expiresAfter($ttl));
        $this->assertEquals($expectedExpiresAt->format(\DateTime::RFC822), $cacheItem->getExpiresAt()->format(\DateTime::RFC822));
    }
}

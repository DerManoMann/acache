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

use Exception;
use MongoClient;
use Radebatz\ACache\MongoDBCache;

/**
 * MongoDBCache tests.
 */
class MongoDBCacheTest extends NamespaceCacheTest
{

    /**
     * Check if mongo is available.
     */
    protected function hasMongo()
    {
        if (class_exists('MongoClient')) {
            try {
                new MongoClient();
                return true;
            } catch (Exception $e) {
                // nope
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (!$this->hasMongo()) {
            $this->markTestSkipped('Skipping Mongo');
        }
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        if (!$this->hasMongo()) {
            return null;
        }

        $mongoClient = new MongoClient();
        $mongoCollection = $mongoClient->cache->entries->drop();

        return array(
            array(new MongoDBCache($mongoClient->cache->entries))
        );
    }

}

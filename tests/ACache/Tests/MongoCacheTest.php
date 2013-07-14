<?php
namespace ACache\Tests;

use MongoClient;
use ACache\MongoCache;

/**
 * MongoCache tests.
 */
class MongoCacheTest extends NamespaceCacheTest
{

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $mongoClient = new MongoClient();
        $mongoCollection = $mongoClient->cache->entries->drop();
        return array(
            array(new MongoCache($mongoClient->cache->entries))
        );
    }

}

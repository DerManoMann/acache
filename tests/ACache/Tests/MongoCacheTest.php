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
     * Check if mongo is available.
     */
    protected function hasMongo()
    {
        return class_exists('MongoClient');
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
            array(new MongoCache($mongoClient->cache->entries))
        );
    }

}

<?php
namespace ACache\Tests;

use Exception;
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
            array(new MongoCache($mongoClient->cache->entries))
        );
    }

}

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

use PDO;
use Radebatz\ACache\PdoCache;

/**
 * PdoCache tests.
 */
class PdoCacheTest extends NamespaceCacheTest
{
    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $pdoDefaults = new PDO('sqlite::memory:');
        // create cache table using naming defaults
        $stmt = $pdoDefaults->prepare('CREATE TABLE cache(
            id      char(64) PRIMARY KEY NOT NULL,
            entry   TEXT NOT NULL,
            expires INT DEFAULT 0
        );');
        $stmt->execute();

        $pdoCustom = new PDO('sqlite::memory:');
        // create cache table using custom config
        $stmt = $pdoCustom->prepare('CREATE TABLE ccache(
            cid      char(64) PRIMARY KEY NOT NULL,
            centry   TEXT NOT NULL,
            cexpires INT DEFAULT 0
        );');
        $stmt->execute();

        return array(
            array(new PdoCache($pdoDefaults)),
            array(new PdoCache($pdoCustom, array(
                't_cache' => 'ccache',
                'c_id' => 'cid',
                'c_entry' => 'centry',
                'c_expires' => 'cexpires',
            ))),
        );
    }

    /**
     * Test args.
     */
    public function testArgs()
    {
        $pdo = new PDO('sqlite::memory:');

        // defaults
        $cache = new PdoCache(new PDO('sqlite::memory:'));
        $config = $this->getProperty($cache, 'config');
        $this->assertNotNull($config);
        $this->assertEquals(array('t_cache' => 'cache', 'c_id' => 'id', 'c_entry' => 'entry', 'c_expires' => 'expires'), $config);

        $customConfig = array(
            't_cache' => 'ccache',
            'c_id' => 'cid',
            'c_entry' => 'centry',
            'c_expires' => 'cexpires',
        );
        $cache = new PdoCache($pdo, $customConfig);
        $config = $this->getProperty($cache, 'config');
        $this->assertNotNull($config);
        $this->assertEquals($customConfig, $config);
    }

    /**
     * Invalid cache provider.
     */
    public function invalidCacheProvider()
    {
        return array(
            array(new PdoCache(new PDO('sqlite::memory:'))),
        );
    }

    /**
     * Test invalid fetch.
     *
     * @dataProvider invalidCacheProvider
     * @expectedException \RuntimeException
     */
    public function testInvalidFetch(PdoCache $cache)
    {
        $cache->fetch('foo');
    }

    /**
     * Test invalid save.
     *
     * @dataProvider invalidCacheProvider
     * @expectedException \RuntimeException
     */
    public function testInvalidSave(PdoCache $cache)
    {
        $cache->save('foo', 'bar');
    }

    /**
     * Test invalid contains.
     *
     * @dataProvider invalidCacheProvider
     * @expectedException \RuntimeException
     */
    public function testInvalidContains(PdoCache $cache)
    {
        $cache->contains('foo');
    }

    /**
     * Test invalid delete.
     *
     * @dataProvider invalidCacheProvider
     * @expectedException \RuntimeException
     */
    public function testInvalidDelete(PdoCache $cache)
    {
        $cache->delete('foo');
    }
}

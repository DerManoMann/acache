<?php
namespace ACache\Tests;

use PDO;
use ACache\PdoCache;

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

}

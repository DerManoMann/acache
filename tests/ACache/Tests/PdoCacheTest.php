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
        $pdo = new PDO('sqlite::memory:');
        // create cache table using naming defaults
        $stmt = $pdo->prepare('CREATE TABLE cache(
            id      char(64) PRIMARY KEY NOT NULL,
            entry   TEXT NOT NULL,
            expires INT DEFAULT 0
        );');
        $stmt->execute();

        return array(
            array(new PdoCache($pdo))
        );
    }

}

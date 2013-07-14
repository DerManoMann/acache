<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ACache;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO cache.
 *
 * This implementation will set the PDO error mode to <code>PDO::ERRMODE_EXCEPTION</code>.
 *
 * List of available database options:
 *  * t_cache:   The name of the cache table [default: <code>cache</code>]
 *  * c_id:      The cache id column [default: <code>id</code>]
 *  * c_entry:   The cache entry column [default: <code>entry</code>]
 *  * c_expires: The expiry timestamp [default: <code>expires</code>]
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class PdoCache extends AbstractPathKeyCache
{
    protected $pdo;
    protected $config;

    /**
     * Create instance.
     *
     * @param \PDO  $pdo    The PDO instance to use.
     * @param array $config Optional configuration of table/column names; default is an empty array to use the defaults.
     */
    public function __construct(PDO $pdo, array $config = array())
    {
        parent::__construct();
        $this->pdo = $pdo;
        // use exception error mode
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->config = array_merge(
            array(
                't_cache' => 'cache',
                'c_id' => 'id',
                'c_entry' => 'entry',
                'c_expires' => 'expires',
            ),
            $config
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        $config = $this->config;

        try {
            $sql = sprintf('SELECT * FROM %s WHERE %s = :id', $config['t_cache'], $config['c_id']);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (1 == count($results)) {
                return unserialize($results[0][$config['c_entry']]);
            }
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Failed fetching cache id %s: %s', $id, $e->getMessage()), 0, $e);
        }

        throw new RuntimeException(sprintf('Cache entry not found id %s', $id));
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        $config = $this->config;

        try {
            $sql = sprintf('SELECT * FROM %s WHERE %s = :id AND (%3$s = 0 OR %3$s > :now)', $config['t_cache'], $config['c_id'], $config['c_expires']);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':now', time(), PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return 0 != count($results);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Failed fetching(contains) cache id %s: %s', $id, $e->getMessage()), 0, $e);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        $config = $this->config;

        // a little naive, but avoids misc issues with particular implementations
        $this->deleteEntry($id);
        try {
            $sql = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (:id, :entry, :expires)', $config['t_cache'], $config['c_id'], $config['c_entry'], $config['c_expires']);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->bindParam(':entry', serialize($entry), PDO::PARAM_STR);
            $expires = $lifeTime ? (int) (time() + $lifeTime) : 0;
            $stmt->bindParam(':expires', $expires, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Failed updating cache id %s: %s', $id, $e->getMessage()), 0, $e);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        $config = $this->config;

        try {
            $sql = sprintf('DELETE FROM %s WHERE %s = :id', $config['t_cache'], $config['c_id']);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Failed deleting cache id %s: %s', $id, $e->getMessage()), 0, $e);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        $config = $this->config;

        if (!$namespace) {
            try {
                $sql = sprintf('DELETE FROM %s', $config['t_cache']);
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            } catch (PDOException $e) {
                throw new RuntimeException(sprintf('Failed flushing cache: %s', $e->getMessage()), 0, $e);
            }
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
            try {
                $sql = sprintf('DELETE FROM %s WHERE %s like :id', $config['t_cache'], $config['c_id']);
                $stmt = $this->pdo->prepare($sql);
                $wcn = $namespace.'%';
                $stmt->bindParam(':id', $wcn, PDO::PARAM_STR);
                $stmt->execute();
            } catch (PDOException $e) {
                throw new RuntimeException(sprintf('Failed flushing cache namespace %s: %s', $namespace, $e->getMessage()), 0, $e);
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        $config = $this->config;

        $size = 0;
        try {
            // do some cleaning up first
            $sql = sprintf('DELETE FROM %s WHERE %2$s != 0 AND %2$s <= :now', $config['t_cache'], $config['c_expires']);
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':now', time(), PDO::PARAM_INT);
            $stmt->execute();

            $cnt = 'cnt';
            $sql = sprintf('SELECT count(*) AS %s FROM %s', $cnt, $config['t_cache']);
            $stmt = $this->pdo->prepare($sql);
            $results = $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (1 == count($results) && array_key_exists($cnt, $results[0])) {
                $size = $results[0][$cnt];
            }
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Failed getting PDO stats %s', $namespace, $e->getMessage()), 0, $e);
        }

        return array(
            Cache::STATS_SIZE => $size,
        );
    }

}

<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache;

/**
 * Abstract base class for path based cache implementations.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
abstract class AbstractPathKeyCache implements CacheInterface
{
    const DEFAULT_NAMESPACE_DELIMITER = '==';
    protected $namespaceDelimiter;
    protected $defaultTimeToLive;

    /**
     * Create instance.
     *
     * @param string $namespaceDelimiter optional namespace delimiter
     * @param int    $defaultTimeToLive  optional default time-to-live value
     */
    public function __construct($namespaceDelimiter = self::DEFAULT_NAMESPACE_DELIMITER, $defaultTimeToLive = 0)
    {
        $this->namespaceDelimiter = $namespaceDelimiter;
        $this->defaultTimeToLive = $defaultTimeToLive;
    }

    /**
     * Default implementation that always returns <code>true</code>.
     *
     * {@inheritdoc}
     */
    public function available()
    {
        return true;
    }

    /**
     * Get the configured namespace delimiter.
     *
     * @return string the namespace delimiter
     */
    public function getNamespaceDelimiter()
    {
        return $this->namespaceDelimiter;
    }

    /**
     * Convert id and namespace to string.
     *
     * @param string       $id        the id
     * @param string|array $namespace the namespace
     *
     * @return string the namespace as string
     */
    protected function namespaceId($id, $namespace)
    {
        $tmp = (array) $namespace;
        $tmp[] = $id;

        return implode($this->namespaceDelimiter, $tmp);
    }

    /**
     * @inheritdoc
     */
    public function fetch($id, $namespace = null)
    {
        if (!$this->contains($id, $namespace)) {
            return null;
        }

        $entry = $this->fetchEntry($this->namespaceId($id, $namespace));

        return $entry['data'];
    }

    /**
     * @inheritdoc
     */
    public function contains($id, $namespace = null)
    {
        return $this->containsEntry($this->namespaceId($id, $namespace));
    }

    /**
     * @inheritdoc
     */
    public function getTimeToLive($id, $namespace = null)
    {
        if ($this->contains($id, $namespace)) {
            $entry = $this->fetchEntry($this->namespaceId($id, $namespace));

            return $entry['expires'] ? ($entry['expires'] - time()) : 0;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimeToLive()
    {
        return $this->defaultTimeToLive;
    }

    /**
     * @inheritdoc
     */
    public function save($id, $data, $lifeTime = null, $namespace = null)
    {
        if (null !== $lifeTime && 0 > $lifeTime) {
            $this->delete($id, $namespace);

            return null;
        }

        $entry = ['data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0)];

        return (bool) $this->saveEntry($this->namespaceId($id, $namespace), $entry, null !== $lifeTime ? (int) $lifeTime : $this->getDefaultTimeToLive());
    }

    /**
     * @inheritdoc
     */
    public function delete($id, $namespace = null)
    {
        return $this->deleteEntry($this->namespaceId($id, $namespace));
    }

    /**
     * Fetch an entry.
     *
     * @param string $id the full id
     *
     * @return array entry
     */
    abstract protected function fetchEntry($id);

    /**
     * Checks if a given id is valid.
     *
     * @param string $id the full id
     *
     * @return bool <code>true</code> if, and only if, an entry exists
     */
    abstract protected function containsEntry($id);

    /**
     * Save an entry.
     *
     * @param string $id       the cache id
     * @param string $entry    The cache entry
     * @param int    $lifeTime The lifetime in seconds. Set to 0 for infinite life time.
     */
    abstract protected function saveEntry($id, $entry, $lifeTime = 0);

    /**
     * Delete a cache entry for the given id.
     *
     * @param string $id the full id
     *
     * @return bool <code>true</code> if, and only if, the entry was deleted
     */
    abstract protected function deleteEntry($id);
}

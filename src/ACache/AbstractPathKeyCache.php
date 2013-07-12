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

/**
 * Abstract base class for path based cache implementations.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
abstract class AbstractPathKeyCache implements Cache
{
    const DEFAULT_NAMESPACE_DELIMITER = '==';
    protected $namespaceDelimiter;

    /**
     * Create instance.
     *
     * @param string $namespaceDelimiter Optional namespace delimiter; default is <code>DEFAULT_NAMESPACE_DELIMITER</code>.
     */
    public function __construct($namespaceDelimiter = self::DEFAULT_NAMESPACE_DELIMITER)
    {
        $this->namespaceDelimiter = $namespaceDelimiter;
    }

    /**
     * Get the configured namespace delimiter.
     *
     * @return string The namespace delimiter.
     */
    public function getNamespaceDelimiter()
    {
        return $this->namespaceDelimiter;
    }

    /**
     * Convert id and namespace to string.
     *
     * @param  string       $id        The id.
     * @param  string|array $namespace The namespace.
     * @return string       The namespace as string.
     */
    protected function namespaceId($id, $namespace)
    {
        $tmp = (array) $namespace;
        $tmp[] = $id;

        return implode($this->namespaceDelimiter, $tmp);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        return $this->containsEntry($this->namespaceId($id, $namespace));
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function save($id, $data, $namespace = null, $lifeTime = 0)
    {
        $entry = array('data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0));

        return (bool) $this->saveEntry($this->namespaceId($id, $namespace), $entry, (int) $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return $this->deleteEntry($this->namespaceId($id, $namespace));
    }

    /**
     * Fetch an entry.
     *
     * @param  string $id The full id.
     * @return array  Entry.
     */
    abstract protected function fetchEntry($id);

    /**
     * Checks if a given id is valid.
     *
     * @param  string  $id The full id.
     * @return boolean <code>true</code> if, and only if, an entry exists.
     */
    abstract protected function containsEntry($id);

    /**
     * Save an entry.
     *
     * @param string $id       The cache id.
     * @param string $entry    The cache entry
     * @param int    $lifeTime The lifetime in seconds. Set to 0 for infinite life time; default is 0.
     */
    abstract protected function saveEntry($id, $entry, $lifeTime = 0);

    /**
     * Delete a cache entry for the given id.
     *
     * @param  string  $id The full id.
     * @return boolean <code>true</code> if, and only if, the entry was deleted.
     */
    abstract protected function deleteEntry($id);

}

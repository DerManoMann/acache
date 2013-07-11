<?php
namespace ACache;

/**
 * Array cache.
 */
class ArrayCache implements Cache {
    const NAMESPACE_DELIMITER = '==';
    protected $data;


    /**
     * Create instance.
     *
     * @param array $data Optional initial cache data; default is an empty array.
     */
    public function __construct(array $data = array()) {
        $this->data = $data;
    }


    /**
     * Convert id and namespace to string.
     *
     * @param string $id The id.
     * @param string|array $namespace The namespace.
     * @return string The namespace as string.
     */
    protected function namespaceId($id, $namespace) {
        $tmp = (array) $namespace;
        $tmp[] = $id;
        return implode(static::NAMESPACE_DELIMITER, $tmp);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null) {
        if (!$this->contains($id, $namespace)) {
            return null;
        }

        $entry = $this->data[$this->namespaceId($id, $namespace)];

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null) {
        $key = $this->namespaceId($id, $namespace);
        if (!array_key_exists($key, $this->data)) {
            return false;
        }

        $entry = $this->data[$key];

        return 0 == $entry['expires'] || $entry['expires'] > time();
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null) {
        if ($this->contains($id, $namespace)) {
            $entry = $this->data[$this->namespaceId($id, $namespace)];
            return $entry['expires'] ? ($entry['expires'] - time()) : 0;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $namespace = null, $lifeTime = 0) {
        $entry = array('data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0));
        $this->data[$this->namespaceId($id, $namespace)] = $entry;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null) {
        unset($this->data[$this->namespaceId($id, $namespace)]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
        if (!$namespace) {
            $this->data = array();
        } else {
            $namespace = implode(static::NAMESPACE_DELIMITER, (array) $namespace);
            foreach ($this->data as $id => $entry) {
                if (0 === strpos($id, $namespace)) {
                    unset($this->data[$id]);
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return array(
            Cache::STATS_SIZE => count($this->data),
        );
    }

}

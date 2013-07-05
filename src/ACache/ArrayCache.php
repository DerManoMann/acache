<?php
namespace ACache;

/**
 * Array cache.
 */
class ArrayCache implements Cache {
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
     * {@inheritDoc}
     */
    public function fetch($id) {
        if (!$this->contains($id)) {
            return null;
        }

        $entry = $this->data[$id];

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id) {
        if (!array_key_exists($id, $this->data)) {
            return false;
        }

        $entry = $this->data[$id];

        return 0 == $entry['expires'] || $entry['expires'] > time();
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id) {
        if ($this->contains($id)) {
            $entry = $this->data[$id];
            return $entry['expires'] ? ($entry['expires'] - time()) : 0;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0) {
        $entry = array('data' => $data, 'expires' => ($lifeTime ? (time() + $lifeTime) : 0));
        $this->data[$id] = $entry;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        unset($this->data[$id]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
        if (!$namespace) {
            $this->data = array();
        } else {
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
        return null;
    }

}

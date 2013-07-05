<?php
namespace ACache;

/**
 * Cache namespace decorator.
 */
class NamespaceCache implements Cache {
    protected $cache;
    protected $namespace;


    /**
     * Create new instance, decorating the given cache.
     *
     * @param Cache $cache The cache to decorate with a namespace.
     * @param string $namespace The namespace.
     */
    public function __construct(Cache $cache, $namespace) {
        $this->cache = $cache;
        $this->namespace = $namespace;
    }

    /**
     * Apply namespace to a given string.
     *
     * @param string $s The string.
     * @return string The namespace'ed string.
     */
    protected function appyNamespace($s) {
        return sprintf('cns[%s]-%s', $this->namespace, $s);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id) {
        return $this->cache->fetch($this->appyNamespace($id));
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id) {
        return $this->cache->contains($this->appyNamespace($id));
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id) {
        return $this->cache->getTimeToLive($this->appyNamespace($id));
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0) {
        return $this->cache->save($this->appyNamespace($id), $data, $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        return $this->cache->delete($this->appyNamespace($id));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
        return $this->cache->flush($this->appyNamespace($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return $this->cache->getStats();
    }

}

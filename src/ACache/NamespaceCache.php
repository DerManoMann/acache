<?php
namespace ACache;

/**
 * Cache namespace decorator.
 *
 * Not specifiying a namespace will make this class a no-op wrapper.
 */
class NamespaceCache implements Cache {
    protected $cache;
    protected $namespaceFormat;


    /**
     * Create new instance, decorating the given cache.
     *
     * @param Cache $cache The cache to decorate with a namespace.
     * @param string $namespace The namespace; default is <code>null</code> for having no namespace at all.
     */
    public function __construct(Cache $cache, $namespace = null) {
        $this->cache = $cache;
        $this->namespaceFormat = $namespace ? sprintf('%s==%%s', $namespace) : '%s';
    }

    /**
     * Apply namespace to a given string.
     *
     * @param string $s The string.
     * @return string The namespace'ed string.
     */
    protected function appyNamespace($s) {
        return sprintf($this->namespaceFormat, $s);
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

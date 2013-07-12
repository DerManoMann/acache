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
 * Cache namespace decorator.
 *
 * Not specifiying a namespace will make this class a no-op wrapper.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class NamespaceCache implements Cache
{
    protected $cache;
    protected $namespace;

    /**
     * Create new instance, decorating the given cache.
     *
     * @param Cache  $cache     The cache to decorate with a namespace.
     * @param string $namespace The namespace; default is <code>null</code> for having no namespace at all.
     */
    public function __construct(Cache $cache, $namespace = null)
    {
        $this->cache = $cache;
        // ensure we have an array
        $this->namespace = (array) $namespace;
    }

    /**
     * Get the decorated cache.
     *
     * @return ACache\Cache The decorated cache instance.
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get the full namespace path.
     *
     * @param  string|array $namespace Namespace - may be <code>null</code>.
     * @return array        The full namespace path.
     */
    protected function getNamespacePath($namespace)
    {
        return array_merge($this->namespace, (array) $namespace);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        return $this->cache->fetch($id, $this->getNamespacePath($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        return $this->cache->contains($id, $this->getNamespacePath($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        return $this->cache->getTimeToLive($id, $this->getNamespacePath($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $namespace = null, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $this->getNamespacePath($namespace), $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return $this->cache->delete($id, $this->getNamespacePath($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        return $this->cache->flush($this->getNamespacePath($namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }

}

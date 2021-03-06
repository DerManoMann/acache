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
 * Cache namespace decorator.
 *
 * Not specifiying a namespace will make this class a no-op wrapper.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class NamespaceCache implements CacheInterface
{
    protected $cache;
    protected $namespace;

    /**
     * Create new instance, decorating the given cache.
     *
     * @param CacheInterface $cache     the cache to decorate with a namespace
     * @param string         $namespace the namespace
     */
    public function __construct(CacheInterface $cache, $namespace = null)
    {
        $this->cache = $cache;
        // ensure we have an array
        $this->namespace = (array) $namespace;
    }

    /**
     * @inheritdoc
     */
    public function available()
    {
        return $this->cache->available();
    }

    /**
     * Get the decorated cache.
     *
     * @return CacheInterface the decorated cache instance
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get the full namespace path.
     *
     * @param string|array $namespace namespace - may be <code>null</code>
     *
     * @return array the full namespace path
     */
    protected function getNamespacePath($namespace)
    {
        return array_merge($this->namespace, (array) $namespace);
    }

    /**
     * @inheritdoc
     */
    public function fetch($id, $namespace = null)
    {
        return $this->cache->fetch($id, $this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function contains($id, $namespace = null)
    {
        return $this->cache->contains($id, $this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function getTimeToLive($id, $namespace = null)
    {
        return $this->cache->getTimeToLive($id, $this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimeToLive()
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function save($id, $data, $lifeTime = null, $namespace = null)
    {
        return $this->cache->save($id, $data, $lifeTime, $this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function delete($id, $namespace = null)
    {
        return $this->cache->delete($id, $this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function flush($namespace = null)
    {
        return $this->cache->flush($this->getNamespacePath($namespace));
    }

    /**
     * @inheritdoc
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}

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
 * XCache cache.
 *
 * The <code>flush()</code> and <code>getStats()</code> methods rely on xcache auth protected features.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 *
 * @see http://xcache.lighttpd.net/wiki/XcacheIni (xcache.admin.enable_auth)
 */
class XCacheCache extends AbstractPathKeyCache
{
    /**
     * @inheritdoc
     */
    public function available()
    {
        return function_exists('xcache_info');
    }

    /**
     * @inheritdoc
     */
    protected function fetchEntry($id)
    {
        return xcache_get($id);
    }

    /**
     * @inheritdoc
     */
    protected function containsEntry($id)
    {
        return xcache_isset($id);
    }

    /**
     * @inheritdoc
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        return xcache_set($id, $entry, (int) $lifeTime);
    }

    /**
     * @inheritdoc
     */
    protected function deleteEntry($id)
    {
        return xcache_unset($id);
    }

    /**
     * @inheritdoc
     */
    public function flush($namespace = null)
    {
        $namespace = null !== $namespace ? implode($this->getNamespaceDelimiter(), (array) $namespace) : null;
        for ($ii = 0, $max = xcache_count(XC_TYPE_VAR); $ii < $max; ++$ii) {
            $block = xcache_list(XC_TYPE_VAR, $ii);
            foreach ($block as $entries) {
                foreach ($entries as $entry) {
                    if (!$namespace || 0 === strpos($entry['name'], $namespace)) {
                        xcache_unset($entry['name']);
                    }
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getStats()
    {
        $size = 0;
        for ($ii = 0, $max = xcache_count(XC_TYPE_VAR); $ii < $max; ++$ii) {
            $block = xcache_list(XC_TYPE_VAR, $ii);
            foreach ($block as $entries) {
                $size += count($entries);
            }
        }

        return [
            CacheInterface::STATS_SIZE => $size,
        ];
    }
}

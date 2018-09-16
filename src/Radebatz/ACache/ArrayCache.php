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
 * Array cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ArrayCache extends AbstractPathKeyCache
{
    protected static $SHARED_DATA = [];
    protected $localData;
    protected $shared;

    /**
     * Create instance.
     *
     * @param array $data   optional initial cache data
     * @param bool  $shared optional flag to use a shared cache within the current process
     */
    public function __construct(array $data = [], $shared = false)
    {
        parent::__construct();

        $this->localData = [];
        $this->shared = $shared;
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        return $this->shared ? static::$SHARED_DATA[$id] : $this->localData[$id];
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return array_key_exists($id, $this->shared ? static::$SHARED_DATA : $this->localData);
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        if ($this->shared) {
            static::$SHARED_DATA[$id] = $entry;
        } else {
            $this->localData[$id] = $entry;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        if ($this->shared) {
            unset(static::$SHARED_DATA[$id]);
        } else {
            unset($this->localData[$id]);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            if ($this->shared) {
                static::$SHARED_DATA = [];
            } else {
                $this->localData = [];
            }
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
            if ($this->shared) {
                foreach (static::$SHARED_DATA as $id => $entry) {
                    if (0 === strpos($id, $namespace)) {
                        unset(static::$SHARED_DATA[$id]);
                    }
                }
            } else {
                foreach ($this->localData as $id => $entry) {
                    if (0 === strpos($id, $namespace)) {
                        unset($this->localData[$id]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return [
            CacheInterface::STATS_SIZE => count($this->shared ? static::$SHARED_DATA : $this->localData),
        ];
    }
}

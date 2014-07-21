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
    protected $data;

    /**
     * Create instance.
     *
     * @param array $data Optional initial cache data.
     */
    public function __construct(array $data = array())
    {
        parent::__construct();

        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        return $this->data[$id];
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return array_key_exists($id, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        $this->data[$id] = $entry;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        unset($this->data[$id]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            $this->data = array();
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
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
    public function getStats()
    {
        return array(
            CacheInterface::STATS_SIZE => count($this->data),
        );
    }

}

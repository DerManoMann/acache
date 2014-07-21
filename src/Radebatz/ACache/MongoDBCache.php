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

use MongoCollection;

/**
 * MongoDB cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class MongoDBCache extends AbstractPathKeyCache
{
    protected $mongoCollection;
    protected $config;

    /**
     * Create instance.
     *
     * @param \MongoCollection $mongoCollection   The mongo collection to use.
     * @param int              $defaultTimeToLive Optional default time-to-live value.
     */
    public function __construct(MongoCollection $mongoCollection, $defaultTimeToLive = 0)
    {
        parent::__construct(self::DEFAULT_NAMESPACE_DELIMITER, $defaultTimeToLive);

        $this->mongoCollection = $mongoCollection;
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchEntry($id)
    {
        if ($centry = $this->mongoCollection->findOne(array('_id' => $id))) {
            return unserialize($centry['entry']);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function containsEntry($id)
    {
        return null !== $this->mongoCollection->findOne(array('_id' => $id));
    }

    /**
     * {@inheritDoc}
     */
    protected function saveEntry($id, $entry, $lifeTime = 0)
    {
        $expires = $lifeTime ? (int) (time() + $lifeTime) : 0;
        $this->mongoCollection->update(
            array('_id' => $id),
            array('_id' => $id, 'entry' => serialize($entry), 'expires' => $expires),
            array('upsert' => true)
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteEntry($id)
    {
        $this->mongoCollection->remove(array('_id' => $id));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        if (!$namespace) {
            $this->mongoCollection->remove();
        } else {
            $namespace = implode($this->getNamespaceDelimiter(), (array) $namespace);
            foreach ($this->mongoCollection->find() as $centry) {
                if (0 === strpos($centry['_id'], $namespace)) {
                    $this->deleteEntry($centry['_id']);
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
            CacheInterface::STATS_SIZE => $this->mongoCollection->find()->count(),
        );
    }

}

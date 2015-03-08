<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ACache\Tests;

use Radebatz\ACache\ArrayCache;

/**
 * ArrayCache tests.
 */
class ArrayCacheTest extends NamespaceCacheTest
{
    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        return array(
            array(new ArrayCache()),
        );
    }
}

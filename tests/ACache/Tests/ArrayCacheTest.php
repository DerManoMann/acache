<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ACache\Tests;

use ACache\ArrayCache;

/**
 * ArrayCache tests
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test basics.
     */
    public function testBasics()
    {
        $cache = new ArrayCache();
        $cache->save('foo', 'bar');
        $cache->save('yin', 'yang');
        $this->assertTrue($cache->contains('yin'));
    }

}

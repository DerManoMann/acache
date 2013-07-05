<?php
namespace ACache\Tests;

use ACache\ArrayCache;

/**
 * ArrayCache tests
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
        $cache->save('ying', 'yang');
        $this->assertTrue($cache->contains('ying'));
    }	

}

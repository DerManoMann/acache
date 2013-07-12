<?php
namespace ACache\Tests;

use ACache\FilesystemCache;

/**
 * FilesystemCache tests.
 */
class FilesystemCacheTest extends NamespaceCacheTest
{

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        $tempfile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        chmod($tempfile, 0777);

        return array(
            array(new FilesystemCache($tempfile))
        );
    }

}

<?php
namespace ACache\Tests;

use ACache\FilesystemCache;

/**
 * FilesystemCache tests.
 */
class FilesystemCacheTest extends NamespaceCacheTest
{

    /**
     * Get a temp directory.
     *
     * @param int $perms File permissions.
     * @return string The directory name.
     */
    protected function getTempDir($perms = 0777)
    {
        $tempfile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        chmod($tempfile, $perms);

        return $tempfile;
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        return array(
            array(new FilesystemCache($this->getTempDir()))
        );
    }


    /**
     * Test not writeable folder.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNotWriteable()
    {
        new FilesystemCache($this->getTempDir(0000));
    }

    /**
     * Test invalid directory.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalid()
    {
        new FilesystemCache(tempnam(sys_get_temp_dir(), 'acache_'));
    }

    /**
     * Test directory.
     */
    public function testDirectory()
    {
        $dir = $this->getTempDir();
        $cache = new FilesystemCache($dir);
        $this->assertEquals($dir, $cache->getDirectory());
    }

}

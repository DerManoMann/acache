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

use Radebatz\ACache\FilesystemCache;

/**
 * FilesystemCache tests.
 */
class FilesystemCacheTest extends NamespaceCacheTest
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        rmdir($this->getTempDir());
    }

    /**
     * Get a temp directory.
     *
     * @param int $perms file permissions
     *
     * @return string the directory name
     */
    protected function getTempDir($perms = 0777)
    {
        $tempdir = __DIR__ . '/_acache';
        if (is_dir($tempdir)) {
            chmod($tempdir, 0777);
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempdir), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $file) {
                if (in_array($file->getBasename(), ['.', '..'])) {
                    continue;
                } elseif ($file->isDir()) {
                    chmod($file->getPathname(), 0777);
                    rmdir($file->getPathname());
                } elseif ($file->isFile() || $file->isLink()) {
                    chmod($file->getPathname(), 0777);
                    unlink($file->getPathname());
                }
            }
            rmdir($tempdir);
        } elseif (file_exists($tempdir)) {
            unlink($tempdir);
        }

        mkdir($tempdir, $perms, true);

        return $tempdir;
    }

    /**
     * Cache provider.
     */
    public function cacheProvider()
    {
        return [
            [new FilesystemCache($this->getTempDir())],
        ];
    }

    /**
     * Test not writeable folder.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNotWriteable()
    {
        $dir = $this->getTempDir(0000);
        if (is_writeable($dir)) {
            $this->markTestSkipped('Seems chmod is not supported here.');
        }

        new FilesystemCache($dir);
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

    /**
     * Test default permissions.
     */
    public function testDefaultPermissions()
    {
        $dir = $this->getTempDir();
        // force the cache to create the actual cache root folder
        $cacheRoot = $dir . '/foo/bar';
        $cache = new FilesystemCache($cacheRoot);
        $this->assertEquals($cacheRoot, $cache->getDirectory());

        // both foo and bar should have 0777 permissions
        foreach ([$dir . '/foo', $cacheRoot] as $path) {
            $actualFilePerms = (int) substr(sprintf('%o', fileperms($path)), -3);
            $this->assertEquals(777, $actualFilePerms);
        }
    }

    /**
     * Test custom permissions.
     */
    public function testCustomPermissions()
    {
        $dir = $this->getTempDir();
        // force the cache to create the actual cache root folder
        $cacheRoot = $dir . '/foo/bar';
        $cache = new FilesystemCache($cacheRoot, ['directory' => ['mode' => 0755], 'file' => ['mode' => 0444]]);
        $this->assertEquals($cacheRoot, $cache->getDirectory());

        // both foo and bar should have 0777 permissions
        foreach ([$dir . '/foo', $cacheRoot] as $path) {
            $actualFilePerms = (int) substr(sprintf('%o', fileperms($path)), -3);
            $this->assertEquals(755, $actualFilePerms);
        }

        $cache->save('sup', 'something');
        $supCachefile = $cache->getFilenameForId('sup');
        $actualFilePerms = (int) substr(sprintf('%o', fileperms($supCachefile)), -3);
        $this->assertEquals(444, $actualFilePerms);
    }

    /**
     * Test soft flush.
     */
    public function testSoftFlush()
    {
        $dir = $this->getTempDir();
        // soft flush
        $cache = new FilesystemCache($dir, [], null, false);

        $cache->save('sup', 'something');
        $supCachefile = $cache->getFilenameForId('sup');
        $this->assertTrue(file_exists($supCachefile));
        $cache->flush();
        $this->assertFalse(file_exists($supCachefile));
        $this->assertTrue(file_exists(dirname($supCachefile)));
    }

    /**
     * Test soft flush.
     */
    public function testHardFlush()
    {
        $dir = $this->getTempDir();
        // hard flush
        $cache = new FilesystemCache($dir, [], null, true);

        $cache->save('sup', 'something');
        $supCachefile = $cache->getFilenameForId('sup');
        $this->assertTrue(file_exists($supCachefile));
        $cache->flush();
        $this->assertFalse(file_exists($supCachefile));
        $this->assertFalse(file_exists(dirname($supCachefile)), dirname($supCachefile));
    }
}

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

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class FilesystemCache implements CacheInterface
{
    const P_DIRECTORY = 'directory';
    const P_FILE = 'file';
    protected $directory;
    protected $permissions;
    protected $keySanitiser;
    protected $hardFlush;

    /**
     * Create instance.
     *
     * @param string   $directory    the root directory of this cache
     * @param array    $permissions  the permissions to be used for all files/directories created
     * @param callable $keySanitiser optional sanitizer to avoid invalid filenames
     * @param bool     $hardFlush    Optional flag to delete both files and direcories on flush()
     */
    public function __construct($directory, array $permissions = [], $keySanitiser = null, $hardFlush = false)
    {
        $this->permissions[static::P_DIRECTORY] = array_merge(
            [
                'owner' => null,
                'group' => null,
                'mode' => 0777,
            ],
            array_key_exists(static::P_DIRECTORY, $permissions) ? $permissions[static::P_DIRECTORY] : []
        );
        $this->permissions[static::P_FILE] = array_merge(
            [
                'owner' => null,
                'group' => null,
                'mode' => 0644,
            ],
            array_key_exists(static::P_FILE, $permissions) ? $permissions[static::P_FILE] : []
        );

        $this->mkdir($directory);
        $this->directory = realpath($directory);
        if (!is_dir($this->directory)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $this->directory));
        }

        if (!is_writable($this->directory)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $this->directory));
        }

        $this->keySanitiser = is_callable($keySanitiser) ? $keySanitiser : function ($key) {
            return $key;
        };
        $this->hardFlush = $hardFlush;
    }

    /**
     * {@inheritdoc}
     */
    public function available()
    {
        return is_dir($this->directory) && is_writeable($this->directory);
    }

    /**
     * Get the configured cache directory.
     *
     * @return string the cache directory path
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Recursive mkdir.
     *
     * @param string $path the path
     */
    protected function mkdir($path)
    {
        if (is_dir($path)) {
            return;
        }

        $perms = $this->permissions[static::P_DIRECTORY];

        $this->mkdir(dirname($path));
        if (!file_exists($path)) {
            mkdir($path, $perms['mode']);
            @chmod($path, $perms['mode']);
            if ($perms['owner']) {
                @chown($path, $perms['owner']);
            }
            if ($perms['group']) {
                @chgrp($path, $perms['group']);
            }
        }
    }

    /**
     * Convert an id into a filename.
     *
     * @param string       $id        the id
     * @param string|array $namespace optional namespace
     *
     * @return string the filename
     */
    public function getFilenameForId($id, $namespace = null)
    {
        $path = array_merge((array) $namespace, str_split(md5($id), 8));

        return implode(DIRECTORY_SEPARATOR, [$this->directory, implode(DIRECTORY_SEPARATOR, $path), $id]);
    }

    /**
     * Get a cache entry for the given id.
     *
     * @param string       $id        the id
     * @param string|array $namespace optional namespace
     * @param bool         $full      flag to indicate whether to include data loading or meta data only
     *
     * @return array the cache entry or <code>null</code>
     */
    protected function getEntryForId($id, $namespace, $full = false)
    {
        $filename = $this->getFilenameForId($id, $namespace);

        if (!is_file($filename)) {
            return null;
        }

        $expires = -1;
        $data = '';

        $fh = fopen($filename, 'r');

        // load  expires
        if (false !== ($line = fgets($fh))) {
            $expires = (int) $line;
        }

        if ($full) {
            // load data too
            while (false !== ($line = fgets($fh))) {
                $data .= $line;
            }
        }

        fclose($fh);

        return ['data' => unserialize($data), 'expires' => $expires];
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id, $namespace = null)
    {
        $id = call_user_func($this->keySanitiser, $id);

        if (!$this->contains($id, $namespace)) {
            return null;
        }

        $entry = $this->getEntryForId($id, $namespace, true);

        return $entry['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id, $namespace = null)
    {
        $id = call_user_func($this->keySanitiser, $id);

        if (!$entry = $this->getEntryForId($id, $namespace, false)) {
            return false;
        }

        return 0 == $entry['expires'] || $entry['expires'] > time();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        $id = call_user_func($this->keySanitiser, $id);

        if (!$entry = $this->getEntryForId($id, $namespace, false)) {
            return false;
        }

        return $entry['expires'] ? ($entry['expires'] - time()) : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTimeToLive()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = null, $namespace = null)
    {
        if (null !== $lifeTime && 0 > $lifeTime) {
            $this->delete($id, $namespace);

            return null;
        }

        $id = call_user_func($this->keySanitiser, $id);

        $filename = $this->getFilenameForId($id, $namespace);
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);

        if (!is_dir($filepath)) {
            $this->mkdir($filepath);
        }

        if (!is_dir($filepath)) {
            return false;
        }

        $lifeTime = null !== $lifeTime ? (int) $lifeTime : $this->getDefaultTimeToLive();
        $expires = $lifeTime ? (time() + $lifeTime) : 0;

        $result = (bool) file_put_contents($filename, $expires . PHP_EOL . serialize($data));

        $perms = $this->permissions[static::P_FILE];
        @chmod($filename, $perms['mode']);
        if ($perms['owner']) {
            @chown($filename, $perms['owner']);
        }
        if ($perms['group']) {
            @chgrp($filename, $perms['group']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, $namespace = null)
    {
        $id = call_user_func($this->keySanitiser, $id);

        return @unlink($this->getFilenameForId($id, $namespace));
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace = null)
    {
        $namespace = implode(DIRECTORY_SEPARATOR, array_merge([$this->directory], (array) $namespace));

        if (!file_exists($namespace)) {
            return true;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($namespace, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $name => $file) {
            if ($file->isFile()) {
                @unlink($name);
            } elseif ($this->hardFlush && $file->isDir() && false == strpos($name, '..')) {
                @rmdir($name);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        $size = 0;
        if (is_dir($this->directory)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory));
            foreach ($iterator as $name => $file) {
                if ($file->isFile()) {
                    ++$size;
                }
            }
        }

        return [
            CacheInterface::STATS_SIZE => $size,
        ];
    }
}

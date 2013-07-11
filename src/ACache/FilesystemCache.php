<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ACache;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem cache.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class FilesystemCache implements Cache
{
    protected $directory;

    /**
     * Create instance.
     *
     * @param string $directory The root directory of this cache.
     */
    public function __construct($directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $directory));
        }

        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $directory));
        }

        $this->directory = realpath($directory);
    }

    /**
     * Convert an id into a filename.
     *
     * @param  string       $id        The id.
     * @param  string|array $namespace Optional namespace; default is <code>null</code> for none.
     * @return string       The filename.
     */
    protected function getFilenameForId($id, $namespace)
    {
        $path = array_merge((array) $namespace, str_split(md5($id), 8));

        return implode(DIRECTORY_SEPARATOR, array($this->directory, implode(DIRECTORY_SEPARATOR, $path), $id));
    }

    /**
     * Get a cache entry for the given id.
     *
     * @param  string       $id        The id.
     * @param  string|array $namespace Optional namespace; default is <code>null</code> for none.
     * @param  boolean      $full      Flag to indicate whether to include data loading or meta data only; default is <code>false</code> for meta data only.
     * @return array        The cache entry or <code>null</code>.
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
            $expires = (integer) $line;
        }

        if ($full) {
            // load data too
            while (false !== ($line = fgets($fh))) {
                $data .= $line;
            }
        }

        fclose($fh);

        return array('data' => unserialize($data), 'expires' => $expires);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id, $namespace = null)
    {
        if (!$this->contains($id, $namespace)) {
            return null;
        }

        $entry = $this->getEntryForId($id, $namespace, true);

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id, $namespace = null)
    {
        if (!$entry = $this->getEntryForId($id, $namespace, false)) {
            return false;
        }

        return 0 == $entry['expires'] || $entry['expires'] > time();
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id, $namespace = null)
    {
        if (!$entry = $this->getEntryForId($id, $namespace, false)) {
            return false;
        }

        return $entry['expires'] ? ($entry['expires'] - time()) : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $namespace = null, $lifeTime = 0)
    {
        $filename = $this->getFilenameForId($id, $namespace);
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);

        if (!is_dir($filepath)) {
            @mkdir($filepath, 0777, true);
        }

        if (!is_dir($filepath)) {
            return false;
        }

        $expires = $lifeTime ? (time() + $lifeTime) : 0;

        return (bool) file_put_contents($filename, $expires . PHP_EOL . serialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id, $namespace = null)
    {
        return @unlink($this->getFilenameForId($id, $namespace));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null)
    {
        $namespace = implode(DIRECTORY_SEPARATOR, array_merge(array($this->directory), (array) $namespace));
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($namespace));
        foreach ($iterator as $name => $file) {
            if ($file->isFile()) {
                @unlink($name);
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory));
        foreach ($iterator as $name => $file) {
            if ($file->isFile()) {
                ++$size;
            }
        }

        return array(
            Cache::STATS_SIZE => $size,
        );;
    }

}

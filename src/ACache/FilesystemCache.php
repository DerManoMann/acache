<?php
namespace ACache;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem cache.
 */
class FilesystemCache implements Cache {
    protected $directory;


    /**
     * Create instance.
     *
     * @param string $directory The root directory of this cache.
     */
    public function __construct($directory) {
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
     * @param string $id The id.
     * @return string The filename.
     */
    protected function getFilenameForId($id) {
        $path = implode(str_split(md5($id), 12), DIRECTORY_SEPARATOR);

        return implode(DIRECTORY_SEPARATOR, array($this->directory, $path, $id));
    }

    /**
     * Get a cache entry for the given id.
     *
     * @param string $id The id.
     * @param boolean $full Flag to indicate whether to include data loading or meta data only; default is <code>false</code> for meta data only.
     * @return array The cache entry or <code>null</code>.
     */
    protected function getEntryForId($id, $full = false) {
        $filename = $this->getFilenameForId($id);

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
    public function fetch($id) {
        if (!$this->contains($id)) {
            return null;
        }

        $entry = $this->getEntryForId($id, true);

        return $entry['data'];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id) {
        if (!$entry = $this->getEntryForId($id, false)) {
            return false;
        }

        return 0 == $entry['expires'] || $entry['expires'] > time();
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToLive($id) {
        if (!$entry = $this->getEntryForId($id, false)) {
            return false;
        }

        return $entry['expires'] ? ($entry['expires'] - time()) : 0;
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0) {
        $filename = $this->getFilenameForId($id);
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);

        if (!is_dir($filepath)) {
            @mkdir($filepath, 0777, true);
        }

        if (!is_dir($filepath)) {
            return false;
        }

        $expires = $lifeTime ? (time() + $lifeTime) : 0;

        return file_put_contents($filename, $expires . PHP_EOL . serialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id) {
        return @unlink($this->getFilenameForId($id));
    }

    /**
     * {@inheritDoc}
     */
    public function flush($namespace = null) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory));

        foreach ($iterator as $name => $file) {
            if (!$namespace || ($file->isFile() && 0 === strpos($file->getFilename(), $namespace))) {
                @unlink($name);
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return null;
    }

}

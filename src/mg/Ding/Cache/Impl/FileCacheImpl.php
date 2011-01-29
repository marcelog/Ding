<?php
/**
 * Simple file cache implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Cache
 * @subpackage Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Cache\Impl;
use Ding\Cache\ICache;
use Ding\Cache\Exception\FileCacheException;

/**
 * Simple file cache implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Cache
 * @subpackage Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class FileCacheImpl implements ICache
{
    /**
     * Holds current instances.
     * @var FileCacheImpl[]
     */
    private static $_instances = false;

    /**
     * Current assigned directory.
     * @var string
     */
    private $_directory;

    /**
     * Returns true if this cache has the given key.
     *
     * @param string $name Key to check for.
     *
     * @return boolean
     */
    public function has($name)
    {
        $filename = implode(
            DIRECTORY_SEPARATOR,
            array($this->_directory, $name)
        );
        return @file_exists($filename);
    }

    /**
     * Returns a cached value.
     *
     * @param string  $name    Key to look for.
     * @param boolean &$result True on success, false otherwise.
     *
     * @return mixed
     */
    public function fetch($name, &$result)
    {
        $result = false;
        $filename = $this->_getFilenameFor($name);
        if (!@file_exists($filename)) {
            return false;
        }
        $data = @file_get_contents($filename);
        $data = unserialize($data);
        $result = ($data != false);
        return $data;
    }

    /**
     * Generates a filename from a given cache key.
     *
     * @param string $name Cache key name
     *
     * @return string
     */
    private function _getFilenameFor($name)
    {
        return $this->_directory . $name;
    }

    /**
     * Stores a key/value.
     *
     * @param string $name  Key to use.
     * @param mixed  $value Value.
     *
     * @return boolean
     */
    public function store($name, $value)
    {
        $filename = $this->_getFilenameFor($name);
        $value = serialize($value);
        return @file_put_contents($filename, $value);
    }

    /**
     * Empties the cache.
     *
     * @todo implement this
	 * @return boolean
     */
    public function flush()
    {
        return false;
    }

    /**
     * Removes a key from the cache.
     *
     * @param string $name Key to remove.
     *
     * @return boolean
     */
    public function remove($name)
    {
        $filename = $this->_getFilenameFor($name);
        return @unlink($filename);
    }

    /**
     * Returns an instance of a cache.
     *
     * @param array $options Options for the cache backend.
     *
     * @return DummyCacheImpl
     */
    public static function getInstance($options = array())
    {
        $dir = $options['directory'];
        if (!isset(self::$_instances[$dir])) {
            $ret = new FileCacheImpl($options);
            self::$_instances[$dir] = $ret;
            $ret->_init();
        } else {
            $ret = self::$_instances[$dir];
        }
        return $ret;
    }

    /**
     * Initializes this cache (creates directories, etc).
     *
     * @throws FileCacheException
     * @return void
     */
    private function _init()
    {
        if (!file_exists($this->_directory)) {
            if (@mkdir($this->_directory, 0750, true) === false) {
                throw new FileCacheException(
                    'Could not create: ' . $this->_directory
                );
            }
        } else if (!is_dir($this->_directory)) {
            throw new FileCacheException(
            	'Not a directory: ' . $this->_directory
            );
        }
    }

    /**
     * Constructor.
     *
	 * @return void
     */
    private function __construct(array $options)
    {
        $this->_directory = $options['directory'] . DIRECTORY_SEPARATOR;
    }
}
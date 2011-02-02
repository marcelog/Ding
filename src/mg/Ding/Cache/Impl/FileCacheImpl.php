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
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
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
        return @file_exists($this->_directory . $name);
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
        $filename = $this->_directory . $name;
        if (!@file_exists($filename)) {
            return false;
        }
        $result = true;
        $data = @file_get_contents($filename);
        $data = unserialize($data);
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
        $value = serialize($value);
        return @file_put_contents($this->_directory . $name, $value);
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
        return @unlink($this->_directory . $name);
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
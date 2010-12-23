<?php
/**
 * Simple dummy cache implementation.
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

/**
 * Simple dummy cache implementation.
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
class DummyCacheImpl implements ICache
{
    /**
     * Holds current instance.
     * @var DummyCacheImpl
     */
    private static $_instance = false;
    
    /**
     * Returns true if this cache has the given key.
     *
     * @param string $name Key to check for.
     * 
     * @return boolean
     */
    public function has($name)
    {
        return false;
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
        return false;
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
        return false;
    }

    /**
     * Empties the cache.
     * 
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
        return false;
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
        if (self::$_instance === false) {
            $ret = new DummyCacheImpl();
            self::$_instance = $ret;
        } else {
            $ret = self::$_instance;
        }
        return $ret;
    }

    /**
     * Constructor.
     * 
	 * @return void
     */
    private function __construct()
    {
    }
}
<?php
/**
 * Simple apc cache implementation.
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
 * Simple apc cache implementation.
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
class APCCacheImpl implements ICache
{
    /**
     * Holds current instance.
     * @var APCCacheImpl
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
        return apc_exists($name);
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
        return apc_fetch($name, $result);
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
        return apc_store($name, $value);
    }

    /**
     * Empties the cache.
     * 
	 * @return boolean
     */
    public function flush()
    {
        return apc_clear_cache();
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
        return apc_delete($name);
    }

    /**
     * Returns an instance of a cache.
     *
     * @param array $options Options for the cache backend.
     * 
     * @return APCCacheImpl
     */
    public static function getInstance($options = array())
    {
        if (self::$_instance === false) {
            $ret = new APCCacheImpl();
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
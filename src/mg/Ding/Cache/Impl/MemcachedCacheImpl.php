<?php
/**
 * Simple memcache implementation that uses memcached extension from php:
 * http://www.php.net/manual/en/book.memcached.php
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
 * Simple memcache implementation that uses memcached extension from php:
 * http://www.php.net/manual/en/book.memcached.php
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
class MemcachedCacheImpl implements ICache
{
    /**
     * Holds current instance.
     * @var MemcachedCacheImpl
     */
    private static $_instance = false;

    /**
     * A Memcached object.
     * @Memcached
     */
    private $_memcached;

    /**
     * Last asked-for object (name) in either has() or get() (see comments
     * below).
     * @var string
     */
    private $_lastAskedName;

    /**
     * Last asked-for object (value) in either has() or get() (see comments
     * below).
     * @var mixed
     */
    private $_lastAskedValue;

    /**
     * Returns true if this cache has the given key.
     *
     * @param string $name Key to check for.
     *
     * @return boolean
     */
    public function has($name)
    {
        $result = false;
        $value = $this->_memcached->get($name);
        if ($this->_memcached->getResultCode() != \Memcached::RES_SUCCESS) {
            return false;
        }
        $this->_lastAskedName = $name;
        $this->_lastAskedValue = $value;
        return true;
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
        // Memcached does not have a way to know if the element is cached
        // without doing a get. so we do the get in has() (see above) but
        // with cache the result because we expect that if has() === true, then
        // someone should be interesting in doing a get() next.
        if ($this->_lastAskedName === $name) {
            $result = true;
            return $this->_lastAskedValue;
        }
        $value = $this->_memcached->get($name);
        if ($this->_memcached->getResultCode() != \Memcached::RES_SUCCESS) {
            return false;
        }
        $this->_lastAskedName = $name;
        $this->_lastAskedValue = $value;
        $result = true;
        return $value;
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
        return $this->_memcached->set($name, $value);
    }

    /**
     * Empties the cache.
     *
	 * @return boolean
     */
    public function flush()
    {
        return $this->_memcached->flush();
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
        return $this->_memcached->delete($name);
    }

    /**
     * Returns an instance of a cache.
     *
     * @param array $options Options for the cache backend.
     *
     * @return MemcachedCacheImpl
     */
    public static function getInstance($options = array())
    {
        if (self::$_instance == false) {
            self::$_instance = new MemcachedCacheImpl($options['filename']);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
	 * @return void
     */
    private function __construct(array $options)
    {
        $this->_memcached = new \Memcached();
        $this->_memcached->addServer($options['host'], $options['port']);
    }
}
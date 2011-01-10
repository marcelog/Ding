<?php
/**
 * Simple generic cache interface.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Cache
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Cache;
use Ding\Cache\ICache;

/**
 * Simple generic cache interface.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Cache
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface ICache
{
    /**
     * Returns true if this cache has the given key.
     *
     * @param string $name Key to check for.
     *
     * @return boolean
     */
    public function has($name);

    /**
     * Returns a cached value.
     *
     * @param string  $name    Key to look for.
     * @param boolean &$result True on success, false otherwise.
     *
     * @return mixed
     */
    public function fetch($name, &$result);

    /**
     * Stores a key/value.
     *
     * @param string $name  Key to use.
     * @param mixed  $value Value.
     *
     * @return boolean
     */
    public function store($name, $value);

    /**
     * Empties the cache.
     *
	 * @return boolean
     */
    public function flush();

    /**
     * Removes a key from the cache.
     *
     * @param string $name Key to remove.
     *
     * @return boolean
     */
    public function remove($name);
}
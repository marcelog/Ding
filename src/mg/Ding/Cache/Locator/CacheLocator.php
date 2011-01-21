<?php
/**
 * Cache Locator. This singleton is used througout the ding architecture in
 * order to find caches for different subsystems.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Cache
 * @subpackage Locator
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Cache\Locator;

use Ding\Cache\Exception\CacheException;
use Ding\Cache\Impl\APCCacheImpl;
use Ding\Cache\Impl\ZendCacheImpl;
use Ding\Cache\Impl\FileCacheImpl;
use Ding\Cache\Impl\MemcachedCacheImpl;
use Ding\Cache\Impl\DummyCacheImpl;

/**
 * Cache Locator. This singleton is used througout the ding architecture in
 * order to find caches for different subsystems.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Cache
 * @subpackage Locator
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class CacheLocator
{
    /**
     * Default options.
     * @var array
     */
    private static $_options = array(
    	'proxy' => array('impl' => 'file', 'directory' => '.'),
        'bdef' => array('impl' => 'apc'),
        'beans' => array('impl' => 'dummy'),
        'annotations' => array('impl' => 'dummy')
    );

    /**
     * Factory for cache implementations.
     *
     * @param array $options Cache options.
     *
     * @throws CacheException
     * @return ICache
     */
    private static function _returnCacheFromImpl($options)
    {
        switch ($options['impl'])
        {
        case 'file':
            return FileCacheImpl::getInstance($options);
        case 'apc':
            return APCCacheImpl::getInstance($options);
        case 'dummy':
            return DummyCacheImpl::getInstance($options);
        case 'zend':
            return ZendCacheImpl::getInstance($options['zend']);
        case 'memcached':
            return MemcachedCacheImpl::getInstance($options['memcached']);
        default:
            throw new CacheException('Invalid cache impl requested');
        }
    }

    /**
     * The container will call this one, in order to setup options. If any
     * option is missing, we use our default options as fallback.
     *
     * @param array $options Cache options.
     *
     * @see CacheLocator::$_options
     * @return void
     */
    public static function configure(array $options)
    {
        self::$_options = array_replace_recursive(self::$_options, $options);
    }

    /**
     * Returns a cache for auto generated proxy classes.
	 *
     * @return ICache
     */
    public static function getProxyCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['proxy']);
    }

    /**
     * Returns a cache for bean definitions.
	 *
     * @return ICache
     */
    public static function getDefinitionsCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['bdef']);
    }

    /**
     * Returns a cache for beans.
	 *
     * @return ICache
     */
    public static function getBeansCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['beans']);
    }

    /**
     * Returns a cache for annotations.
	 *
     * @return ICache
     */
    public static function getAnnotationsCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['annotations']);
    }
}
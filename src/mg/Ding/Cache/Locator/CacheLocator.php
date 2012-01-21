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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
namespace Ding\Cache\Locator;

use Ding\Cache\Exception\CacheException;
use Ding\Cache\Impl\ApcCacheImpl;
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class CacheLocator
{
    /**
     * Default options.
     * @var array
     */
    private static $_options = array(
    	'proxy' => array('impl' => 'dummy'),
        'bdef' => array('impl' => 'dummy'),
        'beans' => array('impl' => 'dummy'),
        'aspect' => array('impl' => 'dummy'),
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
            return ApcCacheImpl::getInstance($options);
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

    /**
     * Returns a cache for aspects.
	 *
     * @return ICache
     */
    public static function getAspectCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['aspect']);
    }
}
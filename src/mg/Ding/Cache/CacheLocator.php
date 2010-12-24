<?php
namespace Ding\Cache;

use Ding\Cache\Exception\CacheException;
use Ding\Cache\Impl\APCCacheImpl;
use Ding\Cache\Impl\FileCacheImpl;
use Ding\Cache\Impl\DummyCacheImpl;

class CacheLocator
{
    private static $_options = array(
    	'proxy' => array('impl' => 'file', 'directory' => '.'),
        'bdef' => array('impl' => 'apc'),
        'beans' => array('impl' => 'dummy')
    );
    
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
        default:
            throw new CacheException('Invalid cache impl requested');
        }
    }

    public static function configure(array $options)
    {
        self::$_options = array_replace_recursive(self::$_options, $options);
    }
    
    public static function getProxyCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['proxy']);
    }
    
    public static function getDefinitionsCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['bdef']);
    }
    
    public static function getBeansCacheInstance()
    {
        return self::_returnCacheFromImpl(self::$_options['beans']);
    }
}
<?php
namespace Ding\Cache;

use Ding\Cache\Exception\CacheException;
use Ding\Cache\Impl\APCCacheImpl;
use Ding\Cache\Impl\FileCacheImpl;
use Ding\Cache\Impl\DummyCacheImpl;

class CacheLocator
{
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

    public static function getProxyCacheInstance(array $options = array())
    {
        //if (isset($options['impl'])) {
        //    return self::_returnCacheFromImpl($options);
        //}
        return FileCacheImpl::getInstance($options);
    }
    
    public static function getDefinitionsCacheInstance(array $options = array())
    {
        //if (isset($options['impl'])) {
        //    return self::_returnCacheFromImpl($options);
        //}
        return APCCacheImpl::getInstance($options);
    }
    
    public static function getBeansCacheInstance(array $options = array())
    {
        //if (isset($options['impl'])) {
        //    return self::_returnCacheFromImpl($options);
        //}
        return DummyCacheImpl::getInstance($options);
    }
}
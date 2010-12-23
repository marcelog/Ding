<?php
namespace Ding\Cache;

use Ding\Cache\Impl\APCCacheImpl;
use Ding\Cache\Impl\DummyCacheImpl;

class CacheLocator
{
    public static function getProxyCacheInstance(array $options = array())
    {
        return APCCacheImpl::getInstance($options);
    }
    
    public static function getDefinitionsCacheInstance(array $options = array())
    {
        return APCCacheImpl::getInstance($options);
    }
    
    public static function getBeansCacheInstance(array $options = array())
    {
        return DummyCacheImpl::getInstance($options);
    }
}
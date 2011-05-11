<?php
/**
 * Aspect Manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://marcelog.github.com/
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
namespace Ding\Aspect;

use Ding\Cache\Locator\CacheLocator;

/**
 * Aspect Manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class AspectManager
{
    /**
     * Holds instance.
     * @var AspectManager
     */
    private static $_instance;

    /**
     * Holds known aspects. Indexed by name.
     * @var AspectDefinition[]
     */
    private $_aspects;

    /**
     * Holds known pointcuts. Indexed by name.
     * @var PointcutDefinition[]
     */
    private $_pointcuts;

    /**
     * Aspect cache to cache aspects and pointcuts.
     * @var ICache
     */
    private $_aspectCache;

    /**
     * Aspect definition providers.
     * @var IAspectProvider[]
     */
    private $_aspectProviders;

    /**
     * Pointcut definition providers.
     * @var IPointcutProvider[]
     */
    private $_pointcutProviders;

    /**
     * Serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        return array();
    }

    /**
     * Adds or overwrites the given aspect.
     *
     * @param AspectDefinition $aspect Aspect.
     *
     * @return void
     */
    public function setAspect(AspectDefinition $aspect)
    {
        $name = $aspect->getName();
        $this->_aspects[$name] = $aspect;
        $this->_aspectCache->store('AspectManagerAspect' . $name, $aspect);
    }

    /**
     * Adds or overwrites the given pointcut.
     *
     * @param PointcutDefinition $pointcut Pointcut.
     *
     * @return void
     */
    public function setPointcut(PointcutDefinition $pointcut)
    {
        $name = $pointcut->getName();
        $this->_pointcuts[$name] = $pointcut;
        $this->_aspectCache->store('AspectManagerPointcut' . $name, $pointcut);
    }

    /**
     * Return all known aspects, indexed by name.
     *
     * @return AspectDefinition[]
     */
    public function getAspects()
    {
        return $this->_aspects;
    }

    /**
     * Returns a PointcutDefinition or false if none found.
     *
     * @param string $pointcut Pointcut id or name.
     *
     * @return PointcutDefinition
     */
    public function getPointcut($pointcut)
    {
        if (isset($this->_pointcuts[$pointcut])) {
            return $this->_pointcuts[$pointcut];
        } else {
            $result = false;
            $value = $this->_aspectCache->fetch('AspectManagerPointcut' . $pointcut, $result);
            if ($result === true) {
                $this->_pointcuts[$pointcut] = $value;
                return $value;
            } else {
                foreach ($this->_pointcutProviders as $provider) {
                    $value = $provider->getPointcut($pointcut);
                    if ($value !== false) {
                        $this->setPointcut($value);
                        return $value;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns an instance for this aspect manager.
     *
     * @return AspectManager
     */
    public static function getInstance()
    {
        if (self::$_instance == false) {
            self::$_instance = new AspectManager();
        }
        return self::$_instance;
    }

    /**
     * Will register an aspect definition provider in this manager.
     *
     * @param IAspectProvider $provider Aspect definition provider.
     *
     * @return void
     */
    public function registerAspectProvider(IAspectProvider $provider)
    {
        $this->_aspectProviders[] = $provider;
        foreach ($provider->getAspects() as $aspect) {
            $this->setAspect($aspect);
        }
    }

    /**
     * Will register a pointcut definition provider in this manager.
     *
     * @param IPointcutProvider $provider Pointcut definition provider.
     *
     * @return void
     */
    public function registerPointcutProvider(IPointcutProvider $provider)
    {
        $this->_pointcutProviders[] = $provider;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    protected function __construct()
    {
        $this->_aspectCache = CacheLocator::getAspectCacheInstance();
        $this->_pointcuts = array();
        $this->_aspects = array();
    }
}
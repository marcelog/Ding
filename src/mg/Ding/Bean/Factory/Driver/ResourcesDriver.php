<?php
/**
 * This driver will open the needed resources.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
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
namespace Ding\Bean\Factory\Driver;
use Ding\Bean\Factory\Filter\ResourceFilter;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\Filter\PropertyFilter;
use Ding\Container\IContainer;

/**
 * This driver will open the needed resources.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class ResourcesDriver implements IBeforeCreateListener
{
    /**
     * Holds current instance.
     * @var ResourcesDriver
     */
    private static $_instance = false;

    public function _apply($value, $factory)
    {
        if (is_string($value) && (strpos($value, 'resource://') === 0)) {
            $value = substr($value, 11);
            $value = $factory->getResource($value);
        }
        return $value;
    }

    /**
     * Recursively, apply filter to property or constructor arguments values.
     *
     * @param BeanPropertyDefinition|BeanConstructoruArgumentDefinition $def
     * @param IContainer $factory Container in use.
     *
     * @return void
     */
    private function _applyFilter($def, IContainer $factory)
    {
        $value = $def->getValue();
        if (is_array($value)) {
            foreach ($value as $def) {
                $this->_applyFilter($def, $factory);
            }
        } else if (is_string($value)) {
            $def->setValue($this->_apply($value, $factory));
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function beforeCreate(IBeanFactory $factory, BeanDefinition $bean)
    {
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property, $factory);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument, $factory);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return ResourcesDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new ResourcesDriver($options);
        }
        return self::$_instance;
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
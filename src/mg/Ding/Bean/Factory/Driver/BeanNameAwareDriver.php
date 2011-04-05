<?php
/**
 * This driver will make the injection of the bean name for beans that
 * implement IBeanNameAware.
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

use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
/**
 * This driver will make the injection of the bean name for beans that
 * implement IBeanNameAware.
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
class BeanNameAwareDriver implements IAfterDefinitionListener
{
    /**
     * Holds current instance.
     * @var BeanNameAwareDriver
     */
    private static $_instance = false;

    public function afterDefinition(IBeanFactory $factory, BeanDefinition $bean)
    {
        $class = $bean->getClass();
        if ($class === false || empty($class)) {
            return $bean;
        }
        $rClass = ReflectionFactory::getClass($class);
        if ($rClass->implementsInterface('Ding\Bean\IBeanNameAware')) {
            $property = new BeanPropertyDefinition('beanName', BeanPropertyDefinition::PROPERTY_SIMPLE, $bean->getName());
            $properties = $bean->getProperties();
            $properties['beanName'] = $property;
            $bean->setProperties($properties);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return BeanNameAwareDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new BeanNameAwareDriver;
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
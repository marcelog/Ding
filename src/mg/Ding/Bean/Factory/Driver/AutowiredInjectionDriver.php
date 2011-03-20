<?php
/**
 * This driver will make the autowired injection.
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
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will make the autowired injection.
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
class AutowiredInjectionDriver implements IAfterCreateListener
{
    /**
     * Cache property setters names.
     * @var array[]
     */
    private $_propertiesNameCache;

    /**
     * Holds current instance.
     * @var AutowiredInjectionDriver
     */
    private static $_instance = false;

    /**
     * This will return the property value from a definition.
     *
     * @param BeanPropertyDefinition $property Property definition.
     *
     * @return mixed
     */
    private function _loadProperty(IBeanFactory $factory, BeanPropertyDefinition $property)
    {
        $value = null;
        if ($property->isBean()) {
            $value = $factory->getBean($property->getValue());
        } else if ($property->isArray()) {
            $value = array();
            foreach ($property->getValue() as $k => $v) {
                $value[$k] = $this->_loadProperty($factory, $v);
            }
        } else if ($property->isCode()) {
            $value = eval($property->getValue());
        } else {
            $value = $property->getValue();
        }
        return $value;
    }

    public function afterCreate(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        $rClass = ReflectionFactory::getClass($beanDefinition->getClass());
        foreach ($rClass->getProperties() as $property) {
            foreach (ReflectionFactory::getAnnotations($property->getDocComment()) as $annotation) {
                if ($annotation->getName() == 'Autowired') {
                    $object = new \ReflectionObject($bean);
                    $property = $object->getProperty($property->getName());


                }
            }
        }
        foreach ($beanDefinition->getProperties() as $property) {
/*
            $propertyName = $property->getName();
            if (isset($this->_propertiesNameCache[$propertyName])) {
                $methodName = $this->_propertiesNameCache[$propertyName];
            } else {
                $methodName = 'set' . ucfirst($propertyName);
                $this->_propertiesNameCache[$propertyName] = $methodName;
            }
            try
            {
                $bean->$methodName($this->_loadProperty($factory, $property));
            } catch (\ReflectionException $exception) {
                throw new BeanFactoryException('Error calling: ' . $methodName);
            }
*/
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return AutowiredInjectionDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new AutowiredInjectionDriver;
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
        $this->_propertiesNameCache = array();
    }
}
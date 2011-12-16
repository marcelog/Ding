<?php
/**
 * This driver will search for @Resource setter methods.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
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
namespace Ding\Bean\Factory\Driver;

use Ding\Reflection\IReflectionFactory;
use Ding\Reflection\IReflectionFactoryAware;
use Ding\Container\IContainerAware;
use Ding\Container\IContainer;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;

/**
 * This driver will search for @Resource setter methods.
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
class AnnotationResourceDriver
    implements IAfterDefinitionListener, IAfterCreateListener,
    IContainerAware, IReflectionFactoryAware
{
    /**
     * Container.
     * @var IContainer
     */
    private $_container;

    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainerAware::setContainer()
     */
    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }
    public function afterCreate($bean, BeanDefinition $beanDefinition)
    {
        $class = $beanDefinition->getClass();
        if (!empty($class)) {
            $rClass = $this->reflectionFactory->getClass($class);
            foreach ($beanDefinition->getAutowiredProperties() as $property) {
                $name = $property->getName();
                $value = $this->_container->getBean($name);
                $rProperty = $rClass->getProperty($name);
                if (!$rProperty->isPublic()) {
                    $rProperty->setAccessible(true);
                    $rProperty->setValue($bean, $value);
                    $rProperty->setAccessible(false);
                } else {
                    $rProperty->setValue($bean, $value);
                }
            }
        }
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        $beanClass = $bean->getClass();
        if (empty($beanClass)) {
            return $bean;
        }
        $annotations = $this->reflectionFactory->getClassAnnotations($beanClass);
        $properties = $bean->getProperties();
        foreach ($annotations as $method => $methodAnnotations) {
            if ($method == 'class') {
                continue;
            }
            if (strpos($method, 'set') !== 0) {
                continue;
            }
            $propName = lcfirst(substr($method, 3));
            foreach ($methodAnnotations as $annotation) {
                if ($annotation->getName() == 'Resource') {
                    $properties[$propName] = new BeanPropertyDefinition(
                        $propName, BeanPropertyDefinition::PROPERTY_BEAN, $propName
                    );
                }
            }
        }
        $bean->setProperties($properties);
        $properties = array();
        foreach ($annotations['class']['properties'] as $property => $propertyAnnotations) {
            foreach ($propertyAnnotations as $annotation) {
                if ($annotation->getName() == 'Resource') {
                    $properties[$property] = new BeanPropertyDefinition(
                        $property, BeanPropertyDefinition::PROPERTY_BEAN, $property
                    );
                }
            }
        }
        $bean->setAutowiredProperties($properties);
        return $bean;
    }
}
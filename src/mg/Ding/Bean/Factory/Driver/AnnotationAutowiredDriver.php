<?php
/**
 * This driver will wire by type.
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

use Ding\Annotation\Annotation;
use Ding\Bean\Factory\Exception\AutowireException;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Container\IContainerAware;
use Ding\Container\IContainer;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Reflection\IReflectionFactory;
use Ding\Reflection\IReflectionFactoryAware;


/**
 * This driver will wire by type.
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
class AnnotationAutowiredDriver
    implements IAfterDefinitionListener, IReflectionFactoryAware, IContainerAware
{
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * @var IContainer
     */
    private $_container;

    private function _autowire($name, Annotation $annotation)
    {
        $properties = array();
        $required = true;
        if ($annotation->hasOption('required')) {
            $required = $annotation->getOptionSinglevalue('required') == 'true';
        }
        $class = $annotation->getOptionSingleValue('type');
        $isArray = strpos(substr($class, -2), "[]") !== false;
        if ($isArray) {
            $class = substr($class, 0, -2);
            $propertyType = BeanPropertyDefinition::PROPERTY_ARRAY;
        } else {
            $propertyType = BeanPropertyDefinition::PROPERTY_BEAN;
        }
        $candidates = $this->_container->getBeansByClass($class);
        if (empty($candidates)) {
            if ($required) {
                throw new AutowireException($name, $class, "Did not find any candidates for autowiring");
            } else {
                return $properties;
            }
        }
        if (!$isArray && count($candidates) > 1) {
            throw new AutowireException($name, $class, "Too many candidates for autowiring");
        }

        if ($isArray) {
            $propertyValue = array();
            foreach ($candidates as $value) {
                $propertyValue[] = new BeanPropertyDefinition(
                    $name, BeanPropertyDefinition::PROPERTY_BEAN, $value
                );
            }
        } else {
            $propertyValue = array_shift($candidates);
        }
        $properties[$name] = new BeanPropertyDefinition(
            $name, $propertyType, $propertyValue
        );
        return $properties;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {

        $class = $bean->getClass();
        $rClass = $this->_reflectionFactory->getClass($class);
        $properties = $bean->getProperties();
        foreach ($rClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $annotations = $this->_reflectionFactory->getPropertyAnnotations($class, $propertyName);
            if (!$annotations->contains('autowired')) {
                continue;
            }
            $annotation = $annotations->getSingleAnnotation('autowired');
            if (!$annotation->hasOption('type')) {
                throw new AutowireException($propertyName, 'Unknown', "Missing type= specification");
            }
            $newProperties = $this->_autowire($propertyName, $annotation);
            $properties = array_merge($properties, $newProperties);
        }
        $bean->setProperties($properties);
        return $bean;
    }

    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->_reflectionFactory = $reflectionFactory;
    }
}

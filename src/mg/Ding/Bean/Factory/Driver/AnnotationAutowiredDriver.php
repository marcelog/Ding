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

use Ding\Bean\BeanConstructorArgumentDefinition;

use Ding\Annotation\Collection;
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

    private function _autowire($name, Annotation $annotation, $class = null)
    {
        $ret = false;
        $required = true;
        if ($annotation->hasOption('required')) {
            $required = $annotation->getOptionSinglevalue('required') == 'true';
        }
        if (!$annotation->hasOption('type')) {
            if ($class === null) {
                throw new AutowireException($name, 'Unknown', "Missing type= specification");
            }
        } else {
            $class = $annotation->getOptionSingleValue('type');
        }
        $isArray = strpos(substr($class, -2), "[]") !== false;
        if ($isArray) {
            $class = substr($class, 0, -2);
            $ret = array();
        }
        $candidates = $this->_container->getBeansByClass($class);
        if (empty($candidates)) {
            if ($required) {
                throw new AutowireException($name, $class, "Did not find any candidates for autowiring");
            } else {
                return array();
            }
        }
        if (!$isArray && count($candidates) > 1) {
            throw new AutowireException($name, $class, "Too many candidates for autowiring");
        }

        if ($isArray) {
            $propertyValue = array();
            foreach ($candidates as $value) {
                $ret[] = $value;
            }
        } else {
            $ret = array_shift($candidates);
        }
        return $ret;
    }

    private function _arrayToBeanProperties($name, $beanNames)
    {
        $ret = array();
        $propertyName = $name;
        if (is_array($beanNames)) {
            $propertyType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propertyValue = array();
            foreach ($beanNames as $value) {
                $propertyValue[] = new BeanPropertyDefinition(
                    $value, BeanPropertyDefinition::PROPERTY_BEAN, $value
                );
            }
        } else {
            $propertyValue = $beanNames;
            $propertyType = BeanPropertyDefinition::PROPERTY_BEAN;
        }
        $ret[$propertyName] = new BeanPropertyDefinition(
            $propertyName, $propertyType, $propertyValue
        );
        return $ret;
    }

    private function _injectProperties(BeanDefinition $bean)
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
            $newProperties = $this->_arrayToBeanProperties(
                $propertyName, $this->_autowire($propertyName, $annotation)
            );
            $properties = array_merge($properties, $newProperties);
        }
        $bean->setProperties($properties);
    }

    private function _injectMethods(BeanDefinition $bean)
    {
        $class = $bean->getClass();
        $rClass = $this->_reflectionFactory->getClass($class);
        $properties = $bean->getProperties();
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            $annotations = $this->_reflectionFactory->getMethodAnnotations($class, $methodName);
            if (!$annotations->contains('autowired')
                || $annotations->contains('bean')
                || $method->isConstructor()
            ) {
                continue;
            }
            $annotation = $annotations->getSingleAnnotation('autowired');
            // Just 1 arg now. Multiple arguments need support in the container side.
            $parameters = $method->getParameters();
            if (empty($parameters)) {
                throw new AutowireException($methodName, $methodName, 'Nothing to autowire (no arguments in method)');
            }
            if (count($parameters) > 1) {
                throw new AutowireException($methodName, $methodName, 'Multiple arguments are not yet supported');
            }
            $type = array_shift($parameters);
            $type = $type->getClass();
            if ($type !== null) {
                $type = $type->getName();
            }
            $newProperties = $this->_arrayToBeanProperties(
                $methodName, $this->_autowire($methodName, $annotation, $type)
            );
            $properties = array_merge($properties, $newProperties);
        }
        $bean->setProperties($properties);
    }

    private function _applyToConstructor(\ReflectionMethod $rMethod, Collection $annotations, BeanDefinition $bean)
    {
        $constructorArguments = $bean->getArguments();
        if (!$annotations->contains('autowired')) {
            return;
        }
        $annotation = $annotations->getSingleAnnotation('autowired');
        foreach ($rMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $type = $parameter->getClass();
            if ($type === null) {
                continue;
            }
            $type = $type->getName();
            $newArgs = $this->_autowire($parameterName, $annotation, $type);
            if (is_array($newArgs)) {
                $values = array();
                foreach ($newArgs as $arg) {
                    $values = new BeanConstructorArgumentDefinition(
                        BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN, $arg, $parameterName
                    );
                }
                $constructorArguments[$parameterName] = new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY, $values, $parameterName
                );
            } else {
                $constructorArguments[$parameterName] = new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN, $newArgs, $parameterName
                );
            }
        }
        $bean->setArguments($constructorArguments);
    }

    private function _injectConstructorArguments(BeanDefinition $bean)
    {
        if ($bean->isCreatedWithFactoryBean()) {
            $factoryMethod = $bean->getFactoryMethod();
            $factoryBean = $bean->getFactoryBean();
            $def = $this->_container->getBeanDefinition($factoryBean);
            $class = $def->getClass();
            $rMethod = $this->_reflectionFactory->getMethod($class, $factoryMethod);
            $annotations = $this->_reflectionFactory->getMethodAnnotations($class, $factoryMethod);
            $this->_applyToConstructor($rMethod, $annotations, $bean);
        } else if ($bean->isCreatedByConstructor()) {
            $class = $bean->getClass();
            $rClass = $this->_reflectionFactory->getClass($class);
            $rMethod = $rClass->getConstructor();
            if ($rMethod) {
                $annotations = $this->_reflectionFactory->getMethodAnnotations(
                    $class, $rMethod->getName()
                );
                $this->_applyToConstructor($rMethod, $annotations, $bean);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        $this->_injectProperties($bean);
        $this->_injectMethods($bean);
        $this->_injectConstructorArguments($bean);
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

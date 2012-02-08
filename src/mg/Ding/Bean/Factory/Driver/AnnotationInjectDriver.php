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
use Ding\Bean\Factory\Exception\InjectByTypeException;
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
class AnnotationInjectDriver
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

    private function _inject($name, Annotation $annotation, $class = null, Annotation $named = null)
    {
        $ret = false;
        $required = true;
        if ($annotation->hasOption('required')) {
            $required = $annotation->getOptionSinglevalue('required') == 'true';
        }
        if (!$annotation->hasOption('type')) {
            if ($class === null) {
                throw new InjectByTypeException($name, 'Unknown', "Missing type= specification");
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
                throw new InjectByTypeException($name, $class, "Did not find any candidates for injecting by type");
            } else {
                return array();
            }
        }
        if (!$isArray && count($candidates) > 1) {
            $preferredName = null;
            if ($named !== null) {
                if (!$named->hasOption('name')) {
                    throw new InjectByTypeException($name, 'Unknown', "@Named needs the name= specification");
                }
                $preferredName = $named->getOptionSingleValue('name');
            }
            if ($preferredName !== null) {
                if (in_array($preferredName, $candidates)) {
                    $candidates = array($preferredName);
                } else {
                    throw new InjectByTypeException($name, 'Unknown', "Specified bean name in @Named not found");
                }
            } else {
                $foundPrimary = false;
                $beans = $candidates;
                foreach ($beans as $beanName) {
                    $beanCandidateDef = $this->_container->getBeanDefinition($beanName);
                    if ($beanCandidateDef->isPrimaryCandidate()) {
                        if ($foundPrimary) {
                            throw new InjectByTypeException(
                                $name, $class, "Too many (primary) candidates for injecting by type"
                            );
                        }
                        $foundPrimary = true;
                        $candidates = array($beanName);
                    }
                }
            }
            if (count($candidates) > 1) {
                throw new InjectByTypeException($name, $class, "Too many candidates for injecting by type");
            }
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

    private function _arrayToConstructorArguments($name, $beanNames)
    {
        $ret = array();
        $type = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN;
        $value = $beanNames;
        if (is_array($beanNames)) {
            $value = array();
            $type = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY;
            foreach ($beanNames as $arg) {
                $value[] = new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN, $arg, $name
                );
            }
        }
        $ret[$name] = new BeanConstructorArgumentDefinition($type, $value, $name);
        return $ret;
    }
    private function _arrayToBeanProperties($name, $beanNames)
    {
        $ret = array();
        $propertyName = $name;
        $propertyValue = $beanNames;
        $propertyType = BeanPropertyDefinition::PROPERTY_BEAN;
        if (is_array($beanNames)) {
            $propertyType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propertyValue = array();
            foreach ($beanNames as $value) {
                $propertyValue[] = new BeanPropertyDefinition(
                    $value, BeanPropertyDefinition::PROPERTY_BEAN, $value
                );
            }
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
            if (!$annotations->contains('inject')) {
                continue;
            }
            $namedAnnotation = null;
            if ($annotations->contains('named')) {
                $namedAnnotation = $annotations->getSingleAnnotation('named');
            }
            $annotation = $annotations->getSingleAnnotation('inject');
            $newProperties = $this->_arrayToBeanProperties(
                $propertyName, $this->_inject($propertyName, $annotation, null, $namedAnnotation)
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
            if (!$annotations->contains('inject')
                || $annotations->contains('bean')
                || $method->isConstructor()
            ) {
                continue;
            }
            $annotation = $annotations->getSingleAnnotation('inject');
            $namedAnnotation = null;
            if ($annotations->contains('named')) {
                $namedAnnotation = $annotations->getSingleAnnotation('named');
            }
            // Just 1 arg now. Multiple arguments need support in the container side.
            $parameters = $method->getParameters();
            if (empty($parameters)) {
                throw new InjectByTypeException($methodName, $methodName, 'Nothing to inject (no arguments in method)');
            }
            if (count($parameters) > 1) {
                throw new InjectByTypeException($methodName, $methodName, 'Multiple arguments are not yet supported');
            }
            $type = array_shift($parameters);
            $type = $type->getClass();
            if ($type !== null) {
                $type = $type->getName();
            }
            $newProperties = $this->_arrayToBeanProperties(
                $methodName, $this->_inject($methodName, $annotation, $type, $namedAnnotation)
            );
            $properties = array_merge($properties, $newProperties);
        }
        $bean->setProperties($properties);
    }

    private function _applyToConstructor(\ReflectionMethod $rMethod, Collection $beanAnnotations, BeanDefinition $bean)
    {
        $constructorArguments = $bean->getArguments();
        if (!$beanAnnotations->contains('inject')) {
            return;
        }
        $annotations = $beanAnnotations->getAnnotations('inject');
        foreach ($annotations as $annotation) {
            if ($annotation->hasOption('type')) {
                if (!$annotation->hasOption('name')) {
                    throw new InjectByTypeException(
                    	'constructor', 'Unknown', 'Cant specify type without name'
                    );
                }
            }
            if ($annotation->hasOption('name')) {
                if (!$annotation->hasOption('type')) {
                    throw new InjectByTypeException(
                    	'constructor', 'Unknown', 'Cant specify name without type'
                    );
                }
                $name = $annotation->getOptionSingleValue('name');
                $type = $annotation->getOptionSingleValue('type');
                $namedAnnotation = null;
                if ($beanAnnotations->contains('named')) {
                    foreach ($beanAnnotations->getAnnotations('named') as $namedAnnotationCandidate) {
                        if ($namedAnnotationCandidate->hasOption('arg')) {
                            $target = $namedAnnotationCandidate->getOptionSingleValue('arg');
                            if ($target == $name) {
                                $namedAnnotation = $namedAnnotationCandidate;
                            }
                        }
                    }
                }
                $newArgs = $this->_inject($name, $annotation, $type, $namedAnnotation);
                $constructorArguments = array_merge(
                    $constructorArguments,
                    $this->_arrayToConstructorArguments($name, $newArgs)
                );
            } else {
                foreach ($rMethod->getParameters() as $parameter) {
                    $parameterName = $parameter->getName();
                    $type = $parameter->getClass();
                    if ($type === null) {
                        continue;
                    }
                    $type = $type->getName();
                    $namedAnnotation = null;
                    if ($beanAnnotations->contains('named')) {
                        foreach ($beanAnnotations->getAnnotations('named') as $namedAnnotationCandidate) {
                            if ($namedAnnotationCandidate->hasOption('arg')) {
                                $target = $namedAnnotationCandidate->getOptionSingleValue('arg');
                                if ($target == $parameterName) {
                                    $namedAnnotation = $namedAnnotationCandidate;
                                }
                            }
                        }
                    }

                    $newArgs = $this->_inject($parameterName, $annotation, $type, $namedAnnotation);
                    $constructorArguments = array_merge(
                        $constructorArguments,
                        $this->_arrayToConstructorArguments($parameterName, $newArgs, $namedAnnotation)
                    );
                }
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

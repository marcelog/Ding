<?php
/**
 * This driver will look up all annotations for the class and each method of
 * the class (of the bean, of course).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Provider
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
namespace Ding\Bean\Provider;

use Ding\Aspect\AspectDefinition;
use Ding\Aspect\PointcutDefinition;
use Ding\Aspect\AspectManager;
use Ding\Aspect\IAspectProvider;
use Ding\Aspect\IAspectManagerAware;
use Ding\Annotation\Collection;
use Ding\Annotation\Annotation as AnnotationDefinition;
use Ding\Reflection\IReflectionFactoryAware;
use Ding\Container\IContainerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Container\IContainer;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Reflection\IReflectionFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This driver will look up all annotations for the class and each method of
 * the class (of the bean, of course).
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
class Annotation
    implements IBeanDefinitionProvider,
    IContainerAware, IReflectionFactoryAware, IAspectManagerAware, IAspectProvider
{
    protected $container;

    /**
     * Target directories to scan for annotated classes.
     * @var string[]
     */
    private $_scanDirs;

    /**
     * @Configuration annotated classes.
     * @var string[]
     */
    private $_configClasses = false;

    /**
     * @Configuration beans (coming from @Configuration annotated classes).
     * @var object[]
     */
    private $_configBeans = false;

    /**
     * Our cache.
     * @var ICache
     */
    private $_cache = false;

    /**
     * Definitions for config beans.
     * @var BeanDefinition[]
     */
    private $_beanDefinitions = array();

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
     * All known beans, indexed by name.
     * @var string[]
     */
    private $_knownBeans = array();
    /**
     * Maps beans from their classes.
     * @var string[]
     */
    private $_knownBeansByClass = array();

    /**
     * This one helps map a bean with a its parent class bean definition.
     * @var string[]
     */
    private $_knownClassesWithValidBeanAnnotations = array();

    /**
     * All beans (names) listening for events will be here.
     * @var string[]
     */
    private $_knownBeansPerEvent = array();

    /**
     * Valid bean annotations.
     * @var string[]
     */
    private $_validBeanAnnotations = array(
    	'controller', 'bean', 'component', 'configuration', 'aspect', 'named'
    );

    private $_aspectManager;

    public function setAspectManager(AspectManager $aspectManager)
    {
        $this->_aspectManager = $aspectManager;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * Returns the bean definition for a parent class of a class (if found). If
     * the parent class has a valid bean annotation (see $_knownBeans) it will
     * be returned.
     *
     * @param string $class
     *
     * @return BeanDefinition|null
     */
    private function _getParentBeanDefinition($class)
    {
        $def = null;
        while($parentRefClass = $this->reflectionFactory->getClass($class)->getParentClass())
        {
            $class = $parentRefClass->getName();
            // Does this class has a valid bean annotation?
            if (isset($this->_knownClassesWithValidBeanAnnotations[$class])) {
                $parentNameBean = $this->_knownClassesWithValidBeanAnnotations[$class];
                return $this->_container->getBeanDefinition($parentNameBean);
            }
        }
        return $def;
    }
    /**
     * Creates a bean definition from the given annotations.
     *
     * @param string $name Bean name.
     * @param string $class Bean class.
     * @param Collection $annotations Annotations with data.
     *
     * @return BeanDefinition
     */
    private function _getBeanDefinition($name, $class, Collection $annotations, $fBean = false, $fMethod = false)
    {
        $def = $this->_getParentBeanDefinition($class);
        if ($def === null) {
            $def = new BeanDefinition($name);
        } else {
            $def = $def->makeChildBean($name);
        }
        if ($fBean) {
            $def->setFactoryBean($fBean);
            $def->setFactoryMethod($fMethod);
        }
        $rClass = $this->reflectionFactory->getClass($class);
        if ($rClass->isAbstract()) {
            $def->makeAbstract();
        } else {
            $def->makeConcrete();
        }
        $def->setClass($class);
        foreach ($this->_validBeanAnnotations as $beanAnnotationName) {
            if ($annotations->contains($beanAnnotationName)) {
                $beanAnnotation = $annotations->getSingleAnnotation($beanAnnotationName);
                break;
            }
        }
        if ($beanAnnotation->hasOption('class')) {
            $def->setClass($beanAnnotation->getOptionSingleValue('class'));
        }
        if ($beanAnnotation->hasOption('name')) {
            $names = $beanAnnotation->getOptionValues('name');
            foreach ($names as $alias) {
                $def->addAlias($alias);
            }
        }
        $def->setName($name);
        if ($annotations->contains('scope')) {
            $annotation = $annotations->getSingleAnnotation('scope');
            if ($annotation->hasOption('value')) {
                $scope = $annotation->getOptionSingleValue('value');
                if ($scope == 'singleton') {
                    $def->setScope(BeanDefinition::BEAN_SINGLETON);
                } else if ($scope == 'prototype') {
                    $def->setScope(BeanDefinition::BEAN_PROTOTYPE);
                } else {
                    throw new BeanFactoryException("Invalid bean scope: $scope");
                }
            }
        } else if ($annotations->contains('singleton')) {
            $def->setScope(BeanDefinition::BEAN_SINGLETON);
        } else if ($annotations->contains('prototype')) {
            $def->setScope(BeanDefinition::BEAN_PROTOTYPE);
        }
        $isPrimary = $annotations->contains('primary');
        if (!$isPrimary) {
            if ($beanAnnotation->hasOption('primary')) {
                $isPrimary = $beanAnnotation->getOptionSingleValue('primary') == 'true';
            }
        }
        if ($isPrimary) {
            $def->markAsPrimaryCandidate();
        }
        if ($annotations->contains('initmethod')) {
            $annotation = $annotations->getSingleAnnotation('initmethod');
            if ($annotation->hasOption('method')) {
                $def->setInitMethod($annotation->getOptionSingleValue('method'));
            }
        }
        if ($annotations->contains('destroymethod')) {
            $annotation = $annotations->getSingleAnnotation('destroymethod');
            if ($annotation->hasOption('method')) {
                $def->setDestroyMethod($annotation->getOptionSingleValue('method'));
            }
        }
        return $def;
    }

    /**
     * Returns all possible names for a bean. If none are found in the bean
     * annotation, the optional $overrideWithName will be chosen. If not, one
     * will be generated.
     *
     * @param AnnotationDefinition $beanAnnotation
     * @param string $overrideWithName
     *
     * @return string[]
     */
    private function _getAllNames(AnnotationDefinition $beanAnnotation, $overrideWithName = false)
    {
        if ($beanAnnotation->hasOption('name')) {
            return $beanAnnotation->getOptionValues('name');
        }
        if ($overrideWithName !== false) {
            return array($overrideWithName);
        }
        return array(BeanDefinition::generateName('Bean'));
    }

    /**
     * Adds a bean to $_knownBeans.
     *
     * @param string $class The class for this bean
     * @param string $key Where this bean has been chosen from (i.e: component, configuration, bean, etc)
     * @param Ding\Annotation\Collection $annotations Annotations for this bean
     * @param string $overrideWithName Override this bean name with this one
     * @param string $fBean An optional factory bean
     * @param string $fMethod An optional factory method
     *
     * @return string The name of the bean recently added
     */
    private function _addBean($class, $key, $annotations, $overrideWithName = false, $fBean = false, $fMethod = false)
    {
        $annotation = $annotations->getSingleAnnotation($key);
        $names = $this->_getAllNames($annotation, $overrideWithName);
        $leadName = $names[0];
        $this->_addBeanToKnownByClass($class, $leadName);
        $this->_knownBeans[$leadName] = array($names, $class, $key, $annotations, $fBean, $fMethod);

        // Dont let @Bean methods interfere with bean parentship.
        if (!$fBean) {
            $this->_knownClassesWithValidBeanAnnotations[$class] = $leadName;
        }
        return $leadName;
    }

    private function _addBeanToKnownByClass($class, $name)
    {
        if (!isset($this->_knownBeansByClass[$class])) {
            $this->_knownBeansByClass[$class] = array();
        }
        $this->_knownBeansByClass[$class][] = $name;
        // Load any parent classes
        $rClass = $this->reflectionFactory->getClass($class);
        $parentClass = $rClass->getParentClass();
        while ($parentClass) {
            $parentClassName = $parentClass->getName();
            $this->_knownBeansByClass[$parentClassName][] = $name;
            $parentClass = $parentClass->getParentClass();
        }

        // Load any interfaces
        foreach ($rClass->getInterfaces() as $interfaceName => $rInterface) {
            $this->_knownBeansByClass[$interfaceName][] = $name;
        }
    }

    private function _traverseConfigClassesAndRegisterForEvents($key, array $configClasses)
    {
        foreach ($configClasses as $class) {
            $rClass = $this->reflectionFactory->getClass($class);
            $annotations = $this->reflectionFactory->getClassAnnotations($class);
            $fBean = $this->_addBean($class, $key, $annotations);
            foreach ($rClass->getMethods() as $method) {
                $methodBeanName = $method->getName();
                $methodBeanAnnotations = $this->reflectionFactory->getMethodAnnotations($class, $methodBeanName);
                if ($methodBeanAnnotations->contains('bean')) {
                    $beanClass = 'stdClass';
                    $beanAnnotation = $methodBeanAnnotations->getSingleAnnotation('bean');
                    if ($beanAnnotation->hasOption('class')) {
                        $beanClass = $beanAnnotation->getOptionSingleValue('class');
                    }
                    $this->_addBean($beanClass, 'bean', $methodBeanAnnotations, $methodBeanName, $fBean, $methodBeanName);
                }
            }
        }
    }

    public function init()
    {
        foreach ($this->_validBeanAnnotations as $beanAnnotationName) {
            $this->_traverseConfigClassesAndRegisterForEvents(
                $beanAnnotationName,
                $this->reflectionFactory->getClassesByAnnotation($beanAnnotationName)
            );
        }
        foreach ($this->_knownBeans as $leadName => $data) {
            $names = $data[0];
            $class = $data[1];
            $key = $data[2];
            $annotations = $data[3];
            $this->registerEventsFor($annotations, $leadName, $class);
        }
    }
    public function getAspects()
    {
        $ret = array();
        $aspectClasses = $this->reflectionFactory->getClassesByAnnotation('aspect');
        foreach ($aspectClasses as $aspectClass) {
            foreach ($this->_knownBeansByClass[$aspectClass] as $beanName) {
                $rClass = $this->reflectionFactory->getClass($aspectClass);
                foreach ($rClass->getMethods() as $rMethod) {
                    $methodName = $rMethod->getName();
                    $annotations = $this->reflectionFactory->getMethodAnnotations($aspectClass, $methodName);
                    if ($annotations->contains('methodinterceptor')) {
                        foreach ($annotations->getAnnotations('methodinterceptor') as $annotation) {
                            $classExpression = $annotation->getOptionSingleValue('class-expression');
                            $expression = $annotation->getOptionSingleValue('expression');
                            $ret[] = $this->_newAspect(
                                $beanName, $classExpression, $expression, $methodName, AspectDefinition::ASPECT_METHOD
                            );

                        }
                    }
                    if ($annotations->contains('exceptioninterceptor')) {
                        foreach ($annotations->getAnnotations('exceptioninterceptor') as $annotation) {
                            $classExpression = $annotation->getOptionSingleValue('class-expression');
                            $expression = $annotation->getOptionSingleValue('expression');
                            $ret[] = $this->_newAspect(
                                $beanName, $classExpression, $expression, $methodName, AspectDefinition::ASPECT_EXCEPTION
                            );
                        }
                    }
                }
            }
        }
        return $ret;
    }

    private function _newAspect($aspectBean, $classExpression, $expression, $method, $type)
    {
        $pointcutName = BeanDefinition::generateName('PointcutAnnotationAspectDriver');
        $pointcutDef = new PointcutDefinition($pointcutName, $expression, $method);
        $aspectName = BeanDefinition::generateName('AnnotationAspected');
        $aspectDef = new AspectDefinition(
            $aspectName, array($pointcutName), $type,
            $aspectBean, $classExpression
        );
        $this->_aspectManager->setPointcut($pointcutDef);
        return $aspectDef;
    }

    /**
     * Looks for @ListensOn and register the bean as an event listener. Since
     * this is an "early" discovery of a bean, a BeanDefinition is generated.
     *
     * @param Collection $annotations Bean Annotations (for classes or methods)
     * @param string $beanName The target bean name.
     * @param string $class The bean class
     *
     * @return void
     */
    protected function registerEventsFor(Collection $annotations, $beanName, $class)
    {
        $rClass = $this->reflectionFactory->getClass($class);
        if ($rClass->isAbstract()) {
            return;
        }
        $this->_registerEventsForBeanName($annotations, $beanName);
        while($rClass = $this->reflectionFactory->getClass($class)->getParentClass()) {
            $class = $rClass->getName();
            $annotations = $this->reflectionFactory->getClassAnnotations($rClass->getName());
            $this->_registerEventsForBeanName($annotations, $beanName);
        }
    }

    private function _registerEventsForBeanName(Collection $annotations, $beanName)
    {
        if ($annotations->contains('listenson')) {
            $annotation = $annotations->getSingleAnnotation('listenson');
            foreach ($annotation->getOptionValues('value') as $eventCsv) {
                foreach (explode(',', $eventCsv) as $eventName) {
                    if (!isset($this->_knownBeansPerEvent[$eventName])) {
                        $this->_knownBeansPerEvent[$eventName] = array();
                    }
                    $this->_knownBeansPerEvent[$eventName][] = $beanName;
                }
            }
        }
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeansListeningOn()
     */
    public function getBeansListeningOn($eventName)
    {
        if (isset($this->_knownBeansPerEvent[$eventName])) {
            return $this->_knownBeansPerEvent[$eventName];
        }
        return array();
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($name)
    {
        foreach ($this->_knownBeans as $leadName => $data) {

            $names = $data[0];
            $class = $data[1];
            $key = $data[2];
            $annotations = $data[3];
            $fBean = $data[4];
            $fMethod = $data[5];
            if (in_array($name, $names)) {
                return $this->_getBeanDefinition($name, $class, $annotations, $fBean, $fMethod);
            }
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeansByClass($class)
    {
        if (isset($this->_knownBeansByClass[$class])) {
            return $this->_knownBeansByClass[$class];
        }
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainerAware::setContainer()
     */
    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }

    public function setCache(\Ding\Cache\ICache $cache)
    {
        $this->_cache = $cache;
    }
    /**
     * Constructor.
     *
     * @param array              $options Optional options.
     * @param \Ding\Cache\ICache $cache   Annotations cache.
     *
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_scanDirs = $options['scanDir'];
        $this->_configClasses = array();
        $this->_beanDefinitions = array();
        $this->_configBeans = array();
    }
}

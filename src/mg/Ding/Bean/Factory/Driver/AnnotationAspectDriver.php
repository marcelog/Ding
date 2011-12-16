<?php
/**
 * This driver will lookup all aspect-annotated beans.
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

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Reflection\IReflectionFactory;
use Ding\Container\IContainerAware;
use Ding\Aspect\IAspectManagerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Container\IContainer;
use Ding\Aspect\AspectManager;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\PointcutDefinition;

/**
 * This driver will lookup all aspect-annotated beans.
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
class AnnotationAspectDriver
    implements IAfterConfigListener, IBeanDefinitionProvider,
    IAspectManagerAware, IContainerAware, IReflectionFactoryAware
{
    /**
     * Aspect manager instance.
     * @var AspectManager
     */
    private $_aspectManager = false;

    /**
     * References cache.
     * @var ICache
     */
    private $_cache;
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
    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($name)
    {
        if (isset($this->_cache[$name])) {
            return $this->_cache[$name];
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeanDefinitionByClass($class)
    {

    }
    private function _newAspect($aspectClass, $classExpression, $expression, $method, $type)
    {
        // Create bean.
        $aspectBeanName = BeanDefinition::generateName('AnnotationAspectedBean');
        $aspectBean = new BeanDefinition($aspectBeanName);
        $aspectBean->setClass($aspectClass);
        $this->_cache[$aspectBeanName] = $aspectBean;
        $pointcutName = BeanDefinition::generateName('PointcutAnnotationAspectDriver');
        $pointcutDef = new PointcutDefinition($pointcutName, $expression, $method);
        $aspectName = BeanDefinition::generateName('AnnotationAspected');
        $aspectDef = new AspectDefinition(
            $aspectName, array($pointcutName), $type,
            $aspectBeanName, $classExpression
        );
        $this->_aspectManager->setPointcut($pointcutDef);
        $this->_aspectManager->setAspect($aspectDef);
    }

    public function setAspectManager(AspectManager $aspectManager)
    {
        $this->_aspectManager = $aspectManager;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig()
    {
        // Create aspects and pointcuts.
        $aspects = $this->reflectionFactory->getClassesByAnnotation('Aspect');
        foreach ($aspects as $name) {
            $annotations = $this->reflectionFactory->getClassAnnotations($name);
            foreach ($annotations as $key => $annotation) {
                if ($key == 'class') {
                    continue;
                }
                if (isset($annotation['MethodInterceptor'])) {
                    $arguments = $annotation['MethodInterceptor']->getArguments();
                    $classExpression = $arguments['class-expression'];
                    $expression = $arguments['expression'];
                    $method = $key;
                    $this->_newAspect(
                        $name, $classExpression, $expression, $method, AspectDefinition::ASPECT_METHOD
                    );
                }
                if (isset($annotation['ExceptionInterceptor'])) {
                    $arguments = $annotation['ExceptionInterceptor']->getArguments();
                    $classExpression = $arguments['class-expression'];
                    $expression = $arguments['expression'];
                    $method = $key;
                    $this->_newAspect(
                        $name, $classExpression, $expression, $method, AspectDefinition::ASPECT_EXCEPTION
                    );
                }
            }
        }
    }

}
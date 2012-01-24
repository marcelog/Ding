<?php
/**
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
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Container\IContainerAware;
use Ding\Mvc\Http\HttpUrlMapper;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\BeanDefinition;
use Ding\Container\IContainer;
use Ding\Reflection\IReflectionFactory;

/**
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
class MvcAnnotationDriver
    implements IAfterConfigListener, IContainerAware, IReflectionFactoryAware
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
    /**
     * Will call HttpUrlMapper::addAnnotatedController to add new mappings
     * from the @Controller annotated classes. Also, creates a new bean
     * definition for every one of them.
     *
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig()
    {
        foreach ($this->reflectionFactory->getClassesByAnnotation('controller') as $controller) {
            foreach ($this->_container->getBeansByClass($controller) as $name) {
                $annotations = $this->reflectionFactory->getClassAnnotations($controller);
                if (!$annotations->contains('requestmapping')) {
                    continue;
                }
                $requestMappings = $annotations->getAnnotations('requestmapping');
                foreach ($requestMappings as $map) {
                    if ($map->hasOption('url')) {
                        foreach ($map->getOptionValues('url') as $url) {
                            HttpUrlMapper::addAnnotatedController($url, $name);
                        }
                    }
                }
            }
        }
    }
}
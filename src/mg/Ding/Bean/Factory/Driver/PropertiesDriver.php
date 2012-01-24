<?php
/**
 * This driver will apply all filters to property values.
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

use Ding\Resource\IResourceLoader;
use Ding\Resource\IResourceLoaderAware;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Container\IContainerAware;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Factory\Filter\ResourceFilter;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Container\IContainer;
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This driver will apply all filters to property values.
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
class PropertiesDriver
    implements IContainerAware,
    IBeanDefinitionProvider, IResourceLoaderAware, IAfterConfigListener
{
    /**
     * Setup flag.
     * @var boolean
     */
    private $_setup = false;

    /**
     * Properties.
     * @var string[]
     */
    private $_properties = array();

    /**
     * Already resolved property names
     * @var string[]
     */
    private $_propertiesNames = array();
    /**
     * Container.
     * @var IContainer
     */
    private $_container;

    /**
     * Injected resource loader.
     * @var IResourceLoader
     */
    private $_resourceLoader;

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
     * @see Ding\Resource.IResourceLoaderAware::setResourceLoader()
     */
    public function setResourceLoader(IResourceLoader $resourceLoader)
    {
        $this->_resourceLoader = $resourceLoader;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig()
    {
        $holder = $this->_container->getBean('PropertiesHolder');
        foreach ($holder->getLocations() as $location) {
            if (is_string($location)) {
                $resource = $this->_resourceLoader->getResource(trim($location));
            } else {
                $resource = $location;
            }
            $contents = stream_get_contents($resource->getStream());
            $this->_container->registerProperties(parse_ini_string($contents, false));
        }
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($name)
    {
        if ($name == 'PropertiesHolder') {
            $bDef = new BeanDefinition('PropertiesHolder');
            $bDef->setClass('Ding\\Helpers\\Properties\\PropertiesHelper');
            return $bDef;
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeansListeningOn()
     */
    public function getBeansListeningOn($eventName)
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeansByClass($class)
    {
        return array();
    }
}
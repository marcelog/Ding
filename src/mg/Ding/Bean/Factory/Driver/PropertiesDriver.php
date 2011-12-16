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
use Ding\Bean\BeanAnnotationDefinition;
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
    implements IAfterDefinitionListener, IContainerAware,
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
     * Apply (search and replace).
     *
     * @param mixed $value Value to replace.
     *
     * @return mixed
     */
    private function _apply($value)
    {
        if (is_string($value)) {
            foreach ($this->_propertiesNames as $k => $v) {
                if (strpos($value, $k) !== false) {
                    if (is_string($v)) {
                        $value = str_replace($k, $v, $value);
                    } else {
                        $value = $v;
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Recursively, apply filter to property or constructor arguments values.
     *
     * @param BeanPropertyDefinition|BeanConstructoruArgumentDefinition $def
     * @param IContainer $factory Container in use.
     *
     * @return void
     */
    private function _applyFilter($def)
    {
        if (!is_object($def)) {
            return;
        }
        $value = $def->getValue();
        if (is_array($value)) {
            foreach ($value as $subDef) {
                $this->_applyFilter($subDef);
            }
        } else if (is_string($value)) {
            $def->setValue($this->_apply($value));
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument);
        }
        return $bean;
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
            $properties = parse_ini_string($contents, false);
            $this->loadProperties($properties);
        }
    }
    protected function loadProperties(array $properties = array())
    {
        foreach (array_keys($properties) as $key) {
            if (strncmp($key, 'php.', 4) === 0) {
                ini_set(substr($key, 4), $properties[$key]);
            }
            /* Change keys. 'property' becomes ${property} */
            $propName = '${' . $key . '}';
            $this->_propertiesNames[$propName] = $properties[$key];
            $this->_properties[$key] = $properties[$key];
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
            $bDef->setScope(BeanDefinition::BEAN_SINGLETON);
            return $bDef;
        }
        return null;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeanDefinitionByClass($class)
    {
        return null;
    }
    /**
     * Constructor.
     *
     * @param array $options Optional options.
     *
     * @return void
     */
    public function __construct(array $options)
    {
        $this->loadProperties($options);
   }
}
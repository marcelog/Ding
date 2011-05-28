<?php
/**
 * This bean will search and replace the properties found in constructor
 * arguments and properties.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage PropertiesHelper
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
namespace Ding\Helpers\Properties;

use Ding\Resource\IResource;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Resource\IResourceLoader;
use Ding\Resource\IResourceLoaderAware;

/**
 * This bean will search and replace the properties found in constructor
 * arguments and properties.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage PropertiesHelper
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class PropertiesHelper
    implements IResourceLoaderAware, IAfterDefinitionListener, IPropertiesHolder
{
    /**
     * Properties.
     * @var array
     */
    private $_properties;

    /**
     * Injected resource loader.
     * @var IResourceLoader
     */
    private $_resourceLoader;

    /**
     * Already resolved property names
     * @var string[]
     */
    private $_propertiesNames;

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
                    $value = str_replace($k, $v, $value);
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
    private function _applyFilter($def, IBeanFactory $factory)
    {
        $value = $def->getValue();
        if (is_array($value)) {
            foreach ($value as $subDef) {
                $this->_applyFilter($subDef, $factory);
            }
        } else if (is_string($value)) {
            $def->setValue($this->_apply($value));
        }
    }

    public function loadProperties(array $properties)
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
     * Set properties files locations.
     *
     * @param mixed[] $locations locations, can be resources or strings.
     *
     * @return void
     */
    public function setLocations($locations)
    {
        foreach ($locations as $location) {
            if ($location instanceof IResource) {
                $resource = $location;
            } else {
                $resource = $this->_resourceLoader->getResource(trim($location));
            }
            $contents = stream_get_contents($resource->getStream());
            $properties = parse_ini_string($contents, false);
            $this->loadProperties($properties);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition $bean)
    {
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property, $factory);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument, $factory);
        }
        return $bean;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_properties = array();
        $this->_propertiesNames = array();
    }
}
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
namespace Ding\Bean\Factory\Driver;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;

use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Factory\Filter\ResourceFilter;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
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
class PropertiesDriver implements IAfterConfigListener, IAfterDefinitionListener
{
    /**
     * Holds current instance.
     * @var PropertiesDriver
     */
    private static $_instance = false;

    /**
     * Setup flag.
     * @var boolean
     */
    private $_setup = false;

    /**
     * Properties.
     * @var array
     */
    private $_properties;

    /**
     * Already resolved property names
     * @var string[]
     */
    private $_propertiesNames;

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
                if (is_string($value) && strpos($value, $k) !== false) {
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

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig(IContainer $factory)
    {
        try
        {
            $bean = $factory->getBean('PropertiesHolder');
            $bean->loadProperties($this->_properties);
        } catch(BeanFactoryException $e) {
            if (!empty($this->_properties)) {
                $bDef = new BeanDefinition('PropertiesHolder');
                $bDef->setClass('Ding\\Helpers\\Properties\\PropertiesHelper');
                $bDef->setScope(BeanDefinition::BEAN_SINGLETON);
                $factory->setBeanDefinition('PropertiesHolder', $bDef);
                $bean = $factory->getBean('PropertiesHolder');
                $bean->loadProperties($this->_properties);
            }
        }
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition $bean)
    {
        if ($this->_setup) {
            return $bean;
        }
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property, $factory);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument, $factory);
        }
        $this->_setup = true;
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $properties Properties to use.
     *
     * @return PropertiesDriver
     */
    public static function getInstance(array $properties)
    {
        if (self::$_instance == false) {
            self::$_instance = new PropertiesDriver($properties);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @param array $options Optional options.
     *
     * @return void
     */
    private function __construct(array $options)
    {
        $this->_properties = $options;
        $this->_propertiesNames = array();
        foreach (array_keys($options) as $key) {
            /* Change keys. 'property' becomes ${property} */
            $propName = '${' . $key . '}';
            $this->_propertiesNames[$propName] = $options[$key];
            $this->_properties[$key] = $options[$key];
        }
   }
}
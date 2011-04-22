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
class PropertiesDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var PropertiesDriver
     */
    private static $_instance = false;

    /**
     * Properties.
     * @var array
     */
    private $_properties;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
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
    }
}
<?php
/**
 * YAML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
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

use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectDefinition;

/**
 * YAML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class BeanYamlDriver implements IBeforeDefinitionListener
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * beans.xml file path.
     * @var string
     */
    private $_filename;

    /**
     * Yaml contents.
     * @var string[]
     */
    private $_yamlFiles = false;

    /**
     * Bean definition template to clone.
     * @var BeanDefinition
     */
    private $_templateBeanDef;

    /**
     * Bean property definition template to clone.
     * @var BeanPropertyDefinition
     */
    private $_templatePropDef;

    /**
     * Bean constructor argument definition template to clone.
     * @var BeanConstructorArgumentDefinition
     */
    private $_templateArgDef;

    /**
     * Aspect definition template to clone.
     * @var AspectDefinition
     */
    private $_templateAspectDef;

    /**
     * Current instance.
     * @var BeanFactoryXmlImpl
     */
    private static $_instance = false;

    /**
     * Initializes yaml contents.
     *
     * @param string $filename
     *
     * @throws BeanFactoryException
     * @return mixed[]
     */
    private function _loadYaml($filename)
    {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('Loading ' . $filename);
        }
        $yamls = array();
        if (!file_exists($filename)) {
            throw new BeanFactoryException($filename . ' not found.');
        }
        $ret = yaml_parse(file_get_contents($filename));
        if ($ret === false) {
            return $ret;
        }
        $yamls[$filename] = $ret;
        if (isset($ret['import'])) {
            foreach ($ret['import'] as $imported) {
                foreach ($this->_loadYaml($imported) as $name => $yaml) {
                    $yamls[$name] = $yaml;
                }
            }
        }
        return $yamls;
    }

    /**
     * Returns an aspect definition.
     *
     * @param string  $name  Method to intercept.
     * @param mixed[] $value Aspect data.
     *
     * @throws BeanFactoryException
     * @return AspectDefinition
     */
    private function _loadAspect($method, $value)
    {
        $aspects = array();
        $aspectBean = $value['ref'];
        $type = $value['type'];
        if ($type == 'method') {
            $type = AspectDefinition::ASPECT_METHOD;
        } else if ($type == 'exception') {
            $type = AspectDefinition::ASPECT_EXCEPTION;
        } else {
            throw new BeanFactoryException('Invalid aspect type');
        }
        return new AspectDefinition($method, $type, $aspectBean);
    }

    /**
     * Returns a property definition.
     *
     * @param string  $name  Property name.
     * @param mixed[] $value Property YAML structure value.
     *
     * @throws BeanFactoryException
     * @return BeanPropertyDefinition
     */
    private function _loadProperty($name, $value)
    {
        if (isset($value['ref'])) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $propValue = $value['ref'];
        } else if (isset($value['eval'])) {
            $propType = BeanPropertyDefinition::PROPERTY_CODE;
            $propValue = $value['eval'];
        } else if (isset($value['bean'])) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $innerBean = 'Bean' . microtime(true);
            $this->_yamlFiles[$this->_filename]['beans'][$innerBean] = $value['bean'];
            $propValue = $innerBean;
        } else if (is_array($value['value'])) {
            $propType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propValue = array();
            foreach ($value['value'] as $key => $inValue) {
                $propValue[$key] = $this->_loadProperty($key, $inValue);
            }
        } else {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = $value['value'];
        }
        return new BeanPropertyDefinition($name, $propType, $propValue);
    }

    /**
     * Returns a constructor argument definition.
     *
     * @param mixed $value Constructor arg YAML structure value.
     *
     * @throws BeanFactoryException
     * @return BeanConstructorArgumentDefinition
     */
    private function _loadConstructorArg($value)
    {
        if (is_array($value)) {
            if (isset($value['ref'])) {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN;
                $argValue = $value['ref'];
            } else if (isset($value['eval'])) {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_CODE;
                $argValue = $value['eval'];
            } else if (isset($value['bean'])) {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN;
                $innerBean = 'Bean' . microtime(true);
                $this->_yamlFiles[$this->_filename]['beans'][$innerBean] = $value['bean'];
                $argValue = $innerBean;
            } else {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY;
                $argValue = array();
                foreach ($value as $key => $inValue) {
                    $argValue[$key] = $this->_loadConstructorArg($inValue);
                }
            }
        } else {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = $value;
        }
        return new BeanConstructorArgumentDefinition($argType, $argValue);
    }

    /**
     * Returns a bean definition.
     *
     * @param string $beanName
     *
     * @throws BeanFactoryException
     * @return BeanDefinition
     */
    private function _loadBean($beanName, BeanDefinition &$bean = null)
    {
        if (!$this->_yamlFiles) {
            $this->_load();
        }
        $beanDef = false;
        foreach($this->_yamlFiles as $name => $yaml) {
            if (isset($yaml['beans'][$beanName])) {
                if ($this->_logger->isDebugEnabled()) {
                    $this->_logger->debug('Found ' . $beanName . ' in ' . $name);
                }
                $beanDef = $yaml['beans'][$beanName];
                break;
            }
        }
        if (false == $beanDef) {
            return $bean;
        }
        if ($bean === null) {
            $bean = clone $this->_templateBeanDef;
        }
        $bean->setName($beanName);
        $bean->setClass($beanDef['class']);
        $bScope = $beanDef['scope'];
        if ($bScope == 'prototype') {
            $bean->setScope(BeanDefinition::BEAN_PROTOTYPE);
        } else if ($bScope == 'singleton') {
            $bean->setScope(BeanDefinition::BEAN_SINGLETON);
        } else {
            throw new BeanFactoryException('Invalid bean scope: ' . $bScope);
        }

        if (isset($beanDef['factory-method'])) {
            $bean->setFactoryMethod($beanDef['factory-method']);
        }

        if (isset($beanDef['depends-on'])) {
            $bean->setDependsOn(explode(',', $beanDef['depends-on']));
        }
        if (isset($beanDef['factory-bean'])) {
            $bean->setFactoryBean($beanDef['factory-bean']);
        }
        if (isset($beanDef['init-method'])) {
            $bean->setInitMethod($beanDef['init-method']);
        }
        if (isset($beanDef['destroy-method'])) {
            $bean->setDestroyMethod($beanDef['destroy-method']);
        }
        $bMethods = $bProps = $bAspects = $constructorArgs = array();
        if (isset($beanDef['properties'])) {
            foreach ($beanDef['properties'] as $name => $value) {
                $bProp = $this->_loadProperty($name, $value);
                $bProps[$name] = $bProp;
            }
        }
        if (isset($beanDef['constructor-args'])) {
            foreach ($beanDef['constructor-args'] as $arg) {
                $constructorArgs[] = $this->_loadConstructorArg($arg);
            }
        }

        if (isset($beanDef['aspects'])) {
            foreach ($beanDef['aspects'] as $key => $value) {
                $bAspects[] = $this->_loadAspect($key, $value);
            }
        }

        if (isset($beanDef['lookup-methods'])) {
            foreach ($beanDef['lookup-methods'] as $name => $beanName) {
                $bMethods[] = array($name, $beanName);
            }
        }
        if (!empty($bProps)) {
            $bean->setProperties($bProps);
        }
        if (!empty($bAspects)) {
            $bean->setAspects($bAspects);
        }
        if (!empty($constructorArgs)) {
            $bean->setArguments($constructorArgs);
        }
        if (!empty($bMethods)) {
            $bean->setMethodInjections($bMethods);
        }
        return $bean;
    }

    /**
     * Initialize YAML contents.
     *
     * @throws BeanFactoryException
     * @return void
     */
    private function _load()
    {
        $this->_yamlFiles = $this->_loadYaml($this->_filename);
        if (empty($this->_yamlFiles)) {
            throw new BeanFactoryException('Could not parse: ' . $this->_filename);
        }
    }
    /**
     * Called from the parent class to get a bean definition.
     *
	 * @param string         $beanName Bean name to get definition for.
	 * @param BeanDefinition $bean     Where to store the data.
	 *
	 * @throws BeanFactoryException
	 * @return BeanDefinition
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition &$bean = null)
    {
        return $this->_loadBean($beanName, $bean);
    }

    /**
     * Returns a instance for this driver.
     *
     * @param array $options Optional options ;)
     *
     * @return BeanYamlDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new BeanYamlDriver($options['filename']);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @param
     *
     * @return void
     */
    protected function __construct($filename)
    {
        $this->_logger = \Logger::getLogger('Ding.Factory.Driver.BeanYamlDriver');
        $this->_beanDefs = array();
        $this->_filename = $filename;
        $this->_yamlFiles = false;
        $this->_templateBeanDef = new BeanDefinition('');
        $this->_templatePropDef = new BeanPropertyDefinition('', 0, null);
        $this->_templateArgDef = new BeanConstructorArgumentDefinition(0, null);
        $this->_templateAspectDef = new AspectDefinition('', 0, '');
    }
}

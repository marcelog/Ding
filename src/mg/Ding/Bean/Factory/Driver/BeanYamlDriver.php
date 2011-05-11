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

use Ding\Aspect\PointcutDefinition;

use Ding\Aspect\AspectManager;
use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\IAspectProvider;
use Ding\Aspect\IPointcutProvider;

/**
 * YAML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class BeanYamlDriver
    implements IBeforeDefinitionListener, IAspectProvider, IPointcutProvider
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * beans.yaml file path.
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
     * Pointcut definition template to clone.
     * @var PointcutDefinition
     */
    private $_templatePointcutDef;

    /**
     * Current instance.
     * @var BeanFactoryYamlImpl
     */
    private static $_instance = false;

    /**
     * The aspect manager.
     * @var AspectManager
     */
    private $_aspectManager = false;

    /**
     * Optional directories to search for bean files.
     * @var string[]
     */
    private $_directories = false;

    /**
     * Serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        return array();
    }

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
        $yamls = array();
        if (is_array($filename)) {
            foreach ($filename as $file) {
                foreach ($this->_loadYaml($file) as $name => $yaml) {
                    $yamls[$name] = $yaml;
                }
            }
            return $yamls;
        }
            $contents = false;
        foreach ($this->_directories as $directory) {
            $fullname = $directory . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($fullname)) {
                continue;
            }
            if ($this->_logger->isDebugEnabled()) {
                $this->_logger->debug('Loading ' . $fullname);
            }
            $contents = @file_get_contents($fullname);
        }
        if ($contents === false) {
            throw new BeanFactoryException($filename . ' not found in ' . print_r($this->_directories, true));
        }
        $ret = @yaml_parse($contents);
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
     * @param mixed[] $aspect Aspect data.
     *
     * @throws BeanFactoryException
     * @return AspectDefinition
     */
    private function _loadAspect($aspect)
    {
        $aspects = array();
        if (isset($aspect['id'])) {
            $name = $aspect['id'];
        } else {
            $name = 'AspectYAML' . rand(1, microtime(true));
        }
        if (isset($aspect['expression'])) {
            $expression = $aspect['expression'];
        } else {
            $expression = '';
        }
        $aspectBean = $aspect['ref'];
        $type = $aspect['type'];
        if ($type == 'method') {
            $type = AspectDefinition::ASPECT_METHOD;
        } else if ($type == 'exception') {
            $type = AspectDefinition::ASPECT_EXCEPTION;
        } else {
            throw new BeanFactoryException('Invalid aspect type');
        }
        $pointcuts = array();
        foreach ($aspect['pointcuts'] as $pointcut) {
            if (isset($pointcut['id'])) {
                $pointcutName = $pointcut['id'];
            } else {
                $pointcutName = 'PointcutYAML' . rand(1, microtime(true));
            }
            if (isset($pointcut['expression'])) {
                $pointcutDef = clone $this->_templatePointcutDef;
                $pointcutDef->setName($pointcutName);
                $pointcutDef->setExpression($pointcut['expression']);
                $pointcutDef->setMethod($pointcut['method']);
                $this->_aspectManager->setPointcut($pointcutDef);
                $pointcuts[] = $pointcutName;
            } else if (isset($pointcut['pointcut-ref'])) {
                $pointcuts[] = $pointcut['pointcut-ref'];
            }
        }
        return new AspectDefinition($name, $pointcuts, $type, $aspectBean, $expression);
    }

    /**
     * Returns a property definition.
     *
     * @param string  $name         Property name.
     * @param mixed[] $value        Property YAML structure value.
     * @param string  $yamlFilename Filename for yaml file.
     *
     * @throws BeanFactoryException
     * @return BeanPropertyDefinition
     */
    private function _loadProperty($name, $value, $yamlFilename)
    {
        if (isset($value['ref'])) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $propValue = $value['ref'];
        } else if (isset($value['eval'])) {
            $propType = BeanPropertyDefinition::PROPERTY_CODE;
            $propValue = $value['eval'];
        } else if (isset($value['bean'])) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $innerBean = 'Bean' . rand(1, microtime(true));
            $this->_yamlFiles[$yamlFilename]['beans'][$innerBean] = $value['bean'];
            $propValue = $innerBean;
        } else if (is_array($value['value'])) {
            $propType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propValue = array();
            foreach ($value['value'] as $key => $inValue) {
                $propValue[$key] = $this->_loadProperty($key, $inValue, $yamlFilename);
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
     * @param mixed  $value Constructor arg YAML structure value.
     * @param string $yamlFilename Filename for yaml file.
     *
     * @throws BeanFactoryException
     * @return BeanConstructorArgumentDefinition
     */
    private function _loadConstructorArg($value, $yamlFilename)
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
                $this->_yamlFiles[$yamlFilename]['beans'][$innerBean] = $value['bean'];
                $argValue = $innerBean;
            } else {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY;
                $argValue = array();
                foreach ($value as $key => $inValue) {
                    $argValue[$key] = $this->_loadConstructorArg($inValue, $yamlFilename);
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
    private function _loadBean($beanName, BeanDefinition $bean = null)
    {
        // This should not be necessary because this driver is also an aspect
        // provider and as such, the AspectManager would already called
        // getAspects() which already has this kind of lazy loading.
        //if (!$this->_yamlFiles) {
        //    $this->_load();
        //}
        $beanDef = false;
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['beans'][$beanName])) {
                if ($this->_logger->isDebugEnabled()) {
                    $this->_logger->debug('Found ' . $beanName . ' in ' . $yamlFilename);
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
                $bProp = $this->_loadProperty($name, $value, $yamlFilename);
                $bProps[$name] = $bProp;
            }
        }
        if (isset($beanDef['constructor-args'])) {
            foreach ($beanDef['constructor-args'] as $arg) {
                $constructorArgs[] = $this->_loadConstructorArg($arg, $yamlFilename);
            }
        }

        if (isset($beanDef['aspects'])) {
            foreach ($beanDef['aspects'] as $name => $aspect) {
                $aspect['id'] = $name;
                $aspectDefinition = $this->_loadAspect($aspect);
                $bAspects[] = $aspectDefinition;
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
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition $bean = null)
    {
        return $this->_loadBean($beanName, $bean);
    }

    public function getPointcut($name)
    {
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['pointcuts'][$name])) {
                if ($this->_logger->isDebugEnabled()) {
                    $this->_logger->debug('Found ' . $name . ' in ' . $yamlFilename);
                }
                $pointcutDef = clone $this->_templatePointcutDef;
                $pointcutDef->setName($name);
                $pointcutDef->setExpression($yaml['pointcuts'][$name]['expression']);
                $pointcutDef->setMethod($yaml['pointcuts'][$name]['method']);
                return $pointcutDef;
            }
        }
        return false;
    }

    public function getAspects()
    {
        $aspects = array();
        if (!$this->_yamlFiles) {
            $this->_load();
        }
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['aspects'])) {
                foreach ($yaml['aspects'] as $aspect) {
                    $aspects[] = $this->_loadAspect($aspect);
                }
            }
        }
        return $aspects;
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
            self::$_instance = new BeanYamlDriver($options);
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
    protected function __construct(array $options)
    {
        $this->_logger = \Logger::getLogger('Ding.Factory.Driver.BeanYamlDriver');
        $this->_beanDefs = array();
        $this->_filename = $options['filename'];
        $this->_directories
            = isset($options['directories'])
            ? $options['directories']
            : array('.');
        $this->_yamlFiles = false;
        $this->_templateBeanDef = new BeanDefinition('');
        $this->_templatePropDef = new BeanPropertyDefinition('', 0, null);
        $this->_templateArgDef = new BeanConstructorArgumentDefinition(0, null);
        $this->_templateAspectDef = new AspectDefinition('', '', 0, '', '');
        $this->_templatePointcutDef = new PointcutDefinition('', '', '');
        $this->_aspectManager = AspectManager::getInstance();
    }
}

<?php
/**
 * YAML bean factory.
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

use Ding\Reflection\IReflectionFactory;
use Ding\Reflection\IReflectionFactoryAware;
use Ding\Logger\ILoggerAware;
use Ding\Aspect\IAspectManagerAware;
use Ding\Container\IContainerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Aspect\PointcutDefinition;
use Ding\Container\IContainer;
use Ding\Aspect\AspectManager;
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
class Yaml implements
    IAfterConfigListener, IAspectProvider,
    IPointcutProvider, IBeanDefinitionProvider, IAspectManagerAware, IContainerAware,
    ILoggerAware, IReflectionFactoryAware
{
    protected $container;

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
     * Bean aliases, pre-scanned
     * @var string[]
     */
    private $_beanAliases = array();

    /**
     * Maps beans from their classes.
     * @var string[]
     */
    private $_knownBeansByClass = array();

    private $_knownBeansPerEvent = array();

    /**
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

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
            $name = BeanDefinition::generateName('AspectYAML');
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
                $pointcutName = BeanDefinition::generateName('PointcutYAML');
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
            $innerBean = BeanDefinition::generateName('Bean');
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
    private function _loadConstructorArg($name, $value, $yamlFilename)
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
                $innerBean = BeanDefinition::generateName('Bean');
                $this->_yamlFiles[$yamlFilename]['beans'][$innerBean] = $value['bean'];
                $argValue = $innerBean;
            } else {
                $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY;
                $argValue = array();
                foreach ($value as $key => $inValue) {
                    $argValue[$key] = $this->_loadConstructorArg(false, $inValue, $yamlFilename);
                }
            }
        } else {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = $value;
        }
        if (is_string($name)) {
            $argName = $name;
        } else {
            $argName = false;
        }
        return new BeanConstructorArgumentDefinition($argType, $argValue, $argName);
    }

    /**
     * Initialize YAML contents.
     *
     * @throws BeanFactoryException
     * @return void
     */
    private function _load()
    {
        if ($this->_yamlFiles !== false) {
            return;
        }
        $this->_yamlFiles = $this->_loadYaml($this->_filename);
        if (empty($this->_yamlFiles)) {
            throw new BeanFactoryException('Could not parse: ' . $this->_filename);
        }
    }

    public function getPointcut($name)
    {
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['pointcuts'][$name])) {
                $pointcutDef = clone $this->_templatePointcutDef;
                $pointcutDef->setName($name);
                $pointcutDef->setExpression($yaml['pointcuts'][$name]['expression']);
                $pointcutDef->setMethod($yaml['pointcuts'][$name]['method']);
                return $pointcutDef;
            }
        }
        return false;
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

    private function _addBeanToKnownByClass($class, $name)
    {
        if (strpos($class, "\${") !== false) {
            return;
        }
        if (!isset($this->_knownBeansByClass[$class])) {
            $this->_knownBeansByClass[$class] = array();
        }
        $this->_knownBeansByClass[$class][] = $name;
        // Load any parent classes
        $rClass = $this->_reflectionFactory->getClass($class);
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

    public function afterConfig()
    {
        $this->_load();
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['alias'])) {
                foreach ($yaml['alias'] as $beanName => $aliases) {
                    $aliases = explode(',', $aliases);
                    foreach ($aliases as $alias) {
                        $alias = trim($alias);
                        $this->_beanAliases[$alias] = $beanName;
                    }
                }
            }
            if (isset($yaml['beans'])) {
                foreach ($yaml['beans'] as $beanName => $beanDef) {
                    if (isset($beanDef['class'])) {
                        $class = $beanDef['class'];
                        if (isset($beanDef['factory-method'])) {
                            // Skip beans that specify class as their factory class
                            if (isset($beanDef['factory-bean'])) {
                                $this->_addBeanToKnownByClass($class, $beanName);
                            }
                        } else {
                            $this->_addBeanToKnownByClass($class, $beanName);
                        }
                    }
                    if (isset($beanDef['name'])) {
                        $aliases = explode(',', $beanDef['name']);
                        foreach ($aliases as $alias) {
                            $alias = trim($alias);
                            $this->_beanAliases[$alias] = $beanName;
                        }
                    }
                    if (isset($beanDef['listens-on'])) {
                        $events = $beanDef['listens-on'];
                        foreach (explode(',', $events) as $eventName) {
                            $eventName = trim($eventName);
                            if (!isset($this->_knownBeansPerEvent[$eventName])) {
                                $this->_knownBeansPerEvent[$eventName] = array();
                            }
                            $this->_knownBeansPerEvent[$eventName][] = $beanName;
                        }
                    }
                }
            }
        }
    }
    public function getAspects()
    {
        $aspects = array();
        $this->_load();
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
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($beanName)
    {
        $beanDef = false;
        if (isset($this->_beanAliases[$beanName])) {
            return $this->getBeanDefinition($this->_beanAliases[$beanName]);
        }
        foreach($this->_yamlFiles as $yamlFilename => $yaml) {
            if (isset($yaml['beans'][$beanName])) {
                $beanDef = $yaml['beans'][$beanName];
                break;
            }
        }
        if (false == $beanDef) {
            return null;
        }
        $bMethods = $bProps = $bAspects = $constructorArgs = array();
        if (isset($beanDef['parent'])) {
            $bean = $this->container->getBeanDefinition($beanDef['parent']);
            $bean = $bean->makeChildBean($beanName);
            $bProps = $bean->getProperties();
            $constructorArgs = $bean->getArguments();
            $bAspects = $bean->getAspects();
            $bMethods = $bean->getMethodInjections();
        } else {
            $bean = clone $this->_templateBeanDef;
        }
        $bean->setName($beanName);
        if (isset($beanDef['class'])) {
            $bean->setClass($beanDef['class']);
        }

        if (isset($beanDef['scope'])) {
            if ($beanDef['scope'] == 'prototype') {
                $bean->setScope(BeanDefinition::BEAN_PROTOTYPE);
            } else if ($beanDef['scope'] == 'singleton') {
                $bean->setScope(BeanDefinition::BEAN_SINGLETON);
            } else {
                throw new BeanFactoryException('Invalid bean scope: ' . $beanDef['scope']);
            }
        }
        if (isset($beanDef['primary'])) {
            $primary = $beanDef['primary'];
            if ($primary == 'true') {
                $bean->markAsPrimaryCandidate();
            }
        }

        if (isset($beanDef['factory-method'])) {
            $bean->setFactoryMethod($beanDef['factory-method']);
        }

        if (isset($beanDef['depends-on'])) {
            $bean->setDependsOn(explode(',', $beanDef['depends-on']));
        }
        if (isset($beanDef['abstract'])) {
            if ($beanDef['abstract'] == 'true') {
                $bean->makeAbstract();
            }
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
        if (isset($beanDef['properties'])) {
            foreach ($beanDef['properties'] as $name => $value) {
                $bProp = $this->_loadProperty($name, $value, $yamlFilename);
                $bProps[$name] = $bProp;
            }
        }
        if (isset($beanDef['constructor-args'])) {
            foreach ($beanDef['constructor-args'] as $name => $arg) {
                $constructorArgs[] = $this->_loadConstructorArg($name, $arg, $yamlFilename);
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
     * @see Ding\Aspect.IAspectManagerAware::setAspectManager()
     */
    public function setAspectManager(AspectManager $aspectManager)
    {
        $this->_aspectManager = $aspectManager;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainerAware::setContainer()
     */
    public function setContainer(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Logger.ILoggerAware::setLogger()
     */
    public function setLogger(\Logger $logger)
    {
        $this->_logger = $logger;
    }


    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->_reflectionFactory = $reflectionFactory;
    }

    /**
     * Constructor.
     *
     * @param array                      $options
     * @param \Ding\Aspect\AspectManager $aspectManager
     *
     * @return void
     */
    public function __construct(array $options)
    {
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
    }
}

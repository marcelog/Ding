<?php
/**
 * XML bean factory.
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
use Ding\Container\IContainerAware;
use Ding\Aspect\IAspectManagerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Container\IContainer;
use Ding\Aspect\PointcutDefinition;
use Ding\Bean\Lifecycle\AfterConfigListener;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\AspectManager;
use Ding\Aspect\IAspectProvider;
use Ding\Aspect\IPointcutProvider;

/**
 * XML bean factory.
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
class Xml implements
    IAfterConfigListener, IAspectProvider, IPointcutProvider,
    IBeanDefinitionProvider, IAspectManagerAware, IContainerAware, ILoggerAware,
    IReflectionFactoryAware
{
    protected $container;
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
     * SimpleXML object.
     * @var SimpleXML[]
     */
    private $_simpleXml;

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
    private $_knownBeansPerEvent = array();
    /**
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * Dont serialize anything here. The container and the aspect manager have
     * references to this driver, and because of IContainerAware or IAspectManagerAware
     * and others, like the methodInjection, some beans and bean definitions will have
     * a reference to the container, so when serializing these beans/definitions, ultimately
     * this driver will be try for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        return array();
    }

    /**
     * Gets xml errors.
     *
     * @return string
     */
    private function _getXmlErrors()
    {
        $errors = '';
        foreach (libxml_get_errors() as $error) {
            $errors .= $error->message . "\n";
        }
        return $errors;
    }

    /**
     * Initializes SimpleXML Object
     *
     * @param string $filename
     *
     * @throws BeanFactoryException
     * @return SimpleXML
     */
    private function _loadXml($filename)
    {
        $xmls = array();
        libxml_use_internal_errors(true);
        if (is_array($filename)) {
            foreach ($filename as $file) {
                $result = $this->_loadXml($file);
                if ($result !== false) {
                    foreach ($result as $name => $xml) {
                        $xmls[$name] = $xml;
                    }
                }
            }
            return $xmls;
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
        $ret = simplexml_load_string($contents);
        if ($ret === false) {
            return $ret;
        }
        $xmls[$filename] = $ret;
        foreach ($ret->xpath("//import") as $imported) {
            $filename = (string)$imported->attributes()->resource;
            foreach ($this->_loadXml($filename) as $name => $xml) {
                $xmls[$name] = $xml;
            }
        }
        return $xmls;
    }

    /**
     * Returns an aspect definition.
     *
     * @param SimpleXML $simpleXmlAspect Aspect node.
     *
     * @throws BeanFactoryException
     * @return AspectDefinition
     */
    private function _loadAspect($simpleXmlAspect)
    {
        $aspects = array();
        $atts = $simpleXmlAspect->attributes();
        if (isset($atts->id)) {
            $name = (string)$atts->id;
        } else {
            $name = BeanDefinition::generateName('AspectXML');
        }
        if (isset($atts->expression)) {
            $expression = (string)$atts->expression;
        } else {
            $expression = '';
        }
        $aspectBean = (string)$atts->ref;
        $type = (string)$atts->type;
        if ($type == 'method') {
            $type = AspectDefinition::ASPECT_METHOD;
        } else if ($type == 'exception') {
            $type = AspectDefinition::ASPECT_EXCEPTION;
        } else {
            throw new BeanFactoryException('Invalid aspect type');
        }
        $pointcuts = array();
        foreach ($simpleXmlAspect->pointcut as $pointcut) {
            $pointcutAtts = $pointcut->attributes();
            if (isset($pointcutAtts->id)) {
                $pointcutName = (string)$pointcutAtts->id;
            } else {
                $pointcutName = BeanDefinition::generateName('PointcutXML');
            }
            if (isset($pointcutAtts->expression)) {
                $pointcut = clone $this->_templatePointcutDef;
                $pointcut->setName($pointcutName);
                $pointcut->setExpression((string)$pointcutAtts->expression);
                $pointcut->setMethod((string)$pointcutAtts->method);
                $this->_aspectManager->setPointcut($pointcut);
                $pointcuts[] = $pointcutName;
            } else if (isset($pointcutAtts->{'pointcut-ref'})) {
                $pointcuts[] = (string)$pointcutAtts->{'pointcut-ref'};
            }
        }
        $aspect = new AspectDefinition($name, $pointcuts, $type, $aspectBean, $expression);
        return $aspect;
    }

    /**
     * Returns a property definition.
     *
     * @param SimpleXML $simpleXmlProperty Property node.
     *
     * @throws BeanFactoryException
     * @return BeanPropertyDefinition
     */
    private function _loadProperty($simpleXmlProperty)
    {
        $propName = (string)$simpleXmlProperty->attributes()->name;
        if (isset($simpleXmlProperty->ref)) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $propValue = (string)$simpleXmlProperty->ref->attributes()->bean;
        } else if (isset($simpleXmlProperty->null)) {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = null;
        } else if (isset($simpleXmlProperty->false)) {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = false;
        } else if (isset($simpleXmlProperty->true)) {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = true;
        } else if (isset($simpleXmlProperty->bean)) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $name = BeanDefinition::generateName('Bean');
            $simpleXmlProperty->bean->addAttribute('id', $name);
            $propValue = $name;
        } else if (isset($simpleXmlProperty->array)) {
            $propType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propValue = array();
            foreach ($simpleXmlProperty->array->entry as $arrayEntry) {
                if (isset($arrayEntry->attributes()->key)) {
                    $key = (string)$arrayEntry->attributes()->key;
                    $propValue[$key] = $this->_loadProperty($arrayEntry);
                } else {
                    $propValue[] = $this->_loadProperty($arrayEntry);
                }
            }
        } else if (isset($simpleXmlProperty->eval)) {
            $propType = BeanPropertyDefinition::PROPERTY_CODE;
            $propValue = (string)$simpleXmlProperty->eval;
        } else {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = (string)$simpleXmlProperty->value;
        }
        return new BeanPropertyDefinition($propName, $propType, $propValue);
    }

    /**
     * Returns a constructor argument definition.
     *
     * @param SimpleXML $simpleXmlArg Argument node.
     *
     * @throws BeanFactoryException
     * @return BeanConstructorArgumentDefinition
     */
    private function _loadConstructorArg($simpleXmlArg)
    {
        if (isset($simpleXmlArg->ref)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN;
            $argValue = (string)$simpleXmlArg->ref->attributes()->bean;
        } else if (isset($simpleXmlArg->bean)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_BEAN;
            $name = BeanDefinition::generateName('Bean');
            $argValue = $name;
            $simpleXmlArg->bean->addAttribute('id', $name);
        } else if (isset($simpleXmlArg->null)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = null;
        } else if (isset($simpleXmlArg->false)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = false;
        } else if (isset($simpleXmlArg->true)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = true;
        } else if (isset($simpleXmlArg->array)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_ARRAY;
            $argValue = array();
            foreach ($simpleXmlArg->array->entry as $arrayEntry) {
                $key = (string)$arrayEntry->attributes()->key;
                $argValue[$key] = $this->_loadConstructorArg($arrayEntry);
            }
        } else if (isset($simpleXmlArg->eval)) {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_CODE;
            $argValue = (string)$simpleXmlArg->eval;
        } else {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = (string)$simpleXmlArg->value;
        }
        if (isset($simpleXmlArg->attributes()->name)) {
            $argName = (string)$simpleXmlArg->attributes()->name;
        } else {
            $argName = false;
        }
        return new BeanConstructorArgumentDefinition($argType, $argValue, $argName);
    }

    /**
     * Initialize SimpleXML.
     *
     * @throws BeanFactoryException
     * @return void
     */
    private function _load()
    {
        if ($this->_simpleXml !== false) {
            return;
        }
        $this->_simpleXml = $this->_loadXml($this->_filename);
        if (empty($this->_simpleXml)) {
            throw new BeanFactoryException(
                'Could not parse: ' . $this->_filename
                . ': ' . $this->_getXmlErrors()
            );
        }
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
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig()
    {
        $this->_load();
        foreach($this->_simpleXml as $xmlName => $xml) {
            $simpleXmlBeans = $xml->xpath("/beans/bean");
            if (!empty($simpleXmlBeans)) {
                foreach ($simpleXmlBeans as $bean) {
                    // Skip anonymous beans
                    if (isset($bean->attributes()->class) && isset($bean->attributes()->id)) {
                        $class = (string)$bean->attributes()->class;
                        $name = (string)$bean->attributes()->id;
                        if (isset($bean->attributes()->{'factory-method'})) {
                            // Skip beans that specify class as their factory class
                            if (isset($bean->attributes()->{'factory-bean'})) {
                                $this->_addBeanToKnownByClass($class, $name);
                            }
                        } else {
                            $this->_addBeanToKnownByClass($class, $name);
                        }
                    }
                    if (isset($bean->attributes()->{'listens-on'})) {
                        $events = (string)$bean->attributes()->{'listens-on'};
                        $beanName = (string)$bean->attributes()->id;
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
    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IAspectProvider::getAspects()
     */
    public function getAspects()
    {
        $aspects = array();
        $this->_load();
        foreach($this->_simpleXml as $xmlName => $xml) {
            $simpleXmlAspect = $xml->xpath("/beans/aspect");
            if (!empty($simpleXmlAspect)) {
                foreach ($simpleXmlAspect as $aspect) {
                    $aspects[] = $this->_loadAspect($aspect);
                }
            }
        }
        return $aspects;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IPointcutProvider::getPointcut()
     */
    public function getPointcut($name)
    {
        foreach($this->_simpleXml as $xmlName => $xml) {
            $simpleXmlPointcut = $xml->xpath("//pointcut[@id='$name']");
            if (!empty($simpleXmlPointcut)) {
                $simpleXmlPointcut = $simpleXmlPointcut[0];
                $pointcutAtts = $simpleXmlPointcut->attributes();
                $pointcutName = (string)$pointcutAtts->id;
                $pointcut = clone $this->_templatePointcutDef;
                $pointcut->setName($pointcutName);
                $pointcut->setExpression((string)$pointcutAtts->expression);
                $pointcut->setMethod((string)$pointcutAtts->method);
                return $pointcut;
            }
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($beanName)
    {
        $simpleXmlBean = false;
        foreach($this->_simpleXml as $name => $xml) {
            $simpleXmlBean = $xml->xpath("//bean[@id='$beanName']");
            if (empty($simpleXmlBean)) {
                $simpleXmlBean = $xml->xpath("//bean[contains(@name, '$beanName')]");
                if (!empty($simpleXmlBean)) {
                    $name = (string)$simpleXmlBean[0]->attributes()->id;
                    return $this->getBeanDefinition($name);
                }
                $simpleXmlBean = $xml->xpath("//alias[@alias='$beanName']");
                if (!empty($simpleXmlBean)) {
                    $name = (string)$simpleXmlBean[0]->attributes()->name;
                    return $this->getBeanDefinition($name);
                }
            } else {
                break;
            }
        }
        if (empty($simpleXmlBean)) {
            return null;
        }
        // asume valid xml (only one bean with that id)
        $simpleXmlBean = $simpleXmlBean[0];
        $bMethods = $bProps = $bAspects = $constructorArgs = array();
        if (isset($simpleXmlBean->attributes()->parent)) {
            $value = (string)$simpleXmlBean->attributes()->parent;
            $bean = $this->container->getBeanDefinition($value);
            $bean = $bean->makeChildBean($beanName);
            $bProps = $bean->getProperties();
            $constructorArgs = $bean->getArguments();
            $bAspects = $bean->getAspects();
            $bMethods = $bean->getMethodInjections();
        } else {
            $bean = clone $this->_templateBeanDef;
        }
        $bean->setName($beanName);
        if (isset($simpleXmlBean->attributes()->class)) {
            $bean->setClass((string)$simpleXmlBean->attributes()->class);
        }
        if (isset($simpleXmlBean->attributes()->scope)) {
            $bScope = (string)$simpleXmlBean->attributes()->scope;
            if ($bScope == 'prototype') {
                $bean->setScope(BeanDefinition::BEAN_PROTOTYPE);
            } else if ($bScope == 'singleton') {
                $bean->setScope(BeanDefinition::BEAN_SINGLETON);
            } else {
                throw new BeanFactoryException('Invalid bean scope: ' . $bScope);
            }
        }
        if (isset($simpleXmlBean->attributes()->{'factory-method'})) {
            $bean->setFactoryMethod(
                (string)$simpleXmlBean->attributes()->{'factory-method'}
            );
        }

        if (isset($simpleXmlBean->attributes()->name)) {
            $aliases = (string)$simpleXmlBean->attributes()->name;
            $aliases = explode(',', $aliases);
            foreach ($aliases as $alias) {
                $bean->addAlias(trim($alias));
            }
        }
        if (isset($simpleXmlBean->attributes()->primary)) {
            $primary = (string)$simpleXmlBean->attributes()->primary;
            if ($primary == 'true') {
                $bean->markAsPrimaryCandidate();
            }
        }
        if (isset($simpleXmlBean->attributes()->{'depends-on'})) {
            $bean->setDependsOn(explode(
            	',',
                (string)$simpleXmlBean->attributes()->{'depends-on'}
            ));
        }
        if (isset($simpleXmlBean->attributes()->abstract)) {
            $value = (string)$simpleXmlBean->attributes()->abstract;
            if ($value == 'true') {
                $bean->makeAbstract();
            }
        }
        if (isset($simpleXmlBean->attributes()->{'factory-bean'})) {
            $bean->setFactoryBean(
                (string)$simpleXmlBean->attributes()->{'factory-bean'}
            );
        }
        if (isset($simpleXmlBean->attributes()->{'init-method'})) {
            $bean->setInitMethod(
                (string)$simpleXmlBean->attributes()->{'init-method'}
            );
        }
        if (isset($simpleXmlBean->attributes()->{'destroy-method'})) {
            $bean->setDestroyMethod(
                (string)$simpleXmlBean->attributes()->{'destroy-method'}
            );
        }
        foreach ($simpleXmlBean->property as $property) {
            $bProp = $this->_loadProperty($property);
            $bProps[$bProp->getName()] = $bProp;
        }
        foreach ($simpleXmlBean->aspect as $aspect) {
            $aspectDefinition = $this->_loadAspect($aspect);
            $bAspects[] = $aspectDefinition;
        }
        foreach ($simpleXmlBean->{'constructor-arg'} as $arg) {
            $constructorArgs[] = $this->_loadConstructorArg($arg);
        }
        foreach ($simpleXmlBean->{'lookup-method'} as $method) {
            $atts = $method->attributes();
            $bMethods[] = array((string)$atts->name, (string)$atts->bean);
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
        $this->_simpleXml = false;
        $this->_directories
            = isset($options['directories'])
            ? $options['directories']
            : array('.');
        $this->_templateBeanDef = new BeanDefinition('');
        $this->_templatePropDef = new BeanPropertyDefinition('', 0, null);
        $this->_templateArgDef = new BeanConstructorArgumentDefinition(0, null);
        $this->_templateAspectDef = new AspectDefinition('', '', 0, '', '');
        $this->_templatePointcutDef = new PointcutDefinition('', '', '');
    }
}

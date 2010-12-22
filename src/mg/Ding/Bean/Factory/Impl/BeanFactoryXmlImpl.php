<?php
/**
 * XML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Factory\Impl;

use Ding\Bean\Factory\BeanFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectDefinition;

/**
 * XML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class BeanFactoryXmlImpl extends BeanFactory
{
    /**
     * Bean definitions
     * @var BeanDefinition[]
     */
    private $_beanDefs;
    
    /**
     * beans.xml file path.
     * @var string
     */
    private $_filename;
    
    /**
     * SimpleXML object.
     * @var SimpleXML
     */
    private $_simpleXml;
    
    /**
     * Current instance.
     * @var BeanFactoryXmlImpl
     */
    private static $_instance = false;
    
    /**
     * Called from the parent class to get a bean definition.
     * 
	 * @param string $beanName Bean name to get definition for.
	 * 
	 * @throws BeanFactoryException
	 * @return BeanDefinition
     */
    public function getBeanDefinition($beanName)
    {
        return
            isset($this->_beanDefs[$beanName])
            ? $this->_beanDefs[$beanName]
            : false
        ;
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
        libxml_use_internal_errors(true);
        if (!file_exists($filename)) {
            throw new BeanFactoryException($filename . ' not found.');
        }
        return simplexml_load_string(file_get_contents($filename));
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
        $aspectBean = (string)$simpleXmlAspect->attributes()->ref;
        $type = (string)$simpleXmlAspect->attributes()->type;
        if ($type == 'method') {
            $type = AspectDefinition::ASPECT_METHOD;
        } else if ($type == 'exception') {
            $type = AspectDefinition::ASPECT_EXCEPTION;
        } else {
            throw new BeanFactoryException('Invalid aspect type');
        }
        foreach ($simpleXmlAspect->pointcut as $pointcut) {
            $aspect = new AspectDefinition(
                (string)$pointcut->attributes()->expression,
                intval($type), 
                $aspectBean 
            );
        }
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
        $propName = $simpleXmlProperty->attributes()->name;
        if (isset($simpleXmlProperty->ref)) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $propValue = $simpleXmlProperty->ref->attributes()->bean;  
        } else {
            $propType = BeanPropertyDefinition::PROPERTY_SIMPLE;
            $propValue = $simpleXmlProperty->value;  
        }
        return new BeanPropertyDefinition(
            (string)$propName, $propType, (string)$propValue
        );
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
            $argValue = $simpleXmlArg->ref->attributes()->bean;  
        } else {
            $argType = BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE;
            $argValue = $simpleXmlArg->value;  
        }
        return new BeanConstructorArgumentDefinition(
            $argType, (string)$argValue
        );
    }
        
    /**
     * Returns a bean definition.
     *  
     * @param SimpleXMLElement $simpleXmlBean
     * 
     * @throws BeanFactoryException
     * @return BeanDefinition
     */
    private function _loadBean($simpleXmlBean)
    {
        $bName = (string)$simpleXmlBean->attributes()->id;
        $bClass = (string)$simpleXmlBean->attributes()->class;
        $bScope = (string)$simpleXmlBean->attributes()->scope;
        if (isset($simpleXmlBean->attributes()->{'factory-method'})) {
            $bFactoryMethod
                = (string)$simpleXmlBean->attributes()->{'factory-method'};
        } else {
            $bFactoryMethod = false;
        }
        if (isset($simpleXmlBean->attributes()->{'factory-bean'})) {
            $bFactoryBean
                = (string)$simpleXmlBean->attributes()->{'factory-bean'};
        } else {
            $bFactoryBean = false;
        }
        if ($bScope == 'prototype') {
            $bScope = BeanDefinition::BEAN_PROTOTYPE;
        } else if ($bScope == 'singleton') {
            $bScope = BeanDefinition::BEAN_SINGLETON;
        } else {
            throw new BeanFactoryException('Invalid bean scope: ' . $bScope);
        }
        $bProps = array();
        $bAspects = array();
        $constructorArgs = array();
        foreach ($simpleXmlBean->property as $property) {
            $bProps[] = $this->_loadProperty($property);
        }
        foreach ($simpleXmlBean->aspect as $aspect) {
            $bAspects[] = $this->_loadAspect($aspect);
        }
        foreach ($simpleXmlBean->{'constructor-arg'} as $arg) {
            $constructorArgs[] = $this->_loadConstructorArg($arg);
        }
        return new BeanDefinition(
            $bName, $bClass, $bScope, $bFactoryMethod, $bFactoryBean,
            $bProps, $bAspects, $constructorArgs
        );
    }
    
    /**
     * Initialize SimpleXML.
     * 
     * @throws BeanFactoryException
     * @return void
     */
    private function _load()
    {
        $this->_simpleXml = $this->_loadXml($this->_filename);
        if (!$this->_simpleXml) {
            throw new BeanFactoryException(
                'Could not parse: ' . $this->_filename
                . ': ' . $this->_getXmlErrors()
            );
        }
        $this->_beanDefs = array();
        foreach ($this->_simpleXml->bean as $bean) {
            $newBean = $this->_loadBean($bean);
            $this->_beanDefs[$newBean->getName()] = $newBean;         
        }
    }

    /**
     * Returns a instance for this factory.
     * 
     * @param string $filename beans.xml path.
     *
     * @return BeanFactoryXmlImpl
     */
    public static function getInstance($filename)
    {
        if (self::$_instance == false) {
            self::$_instance = new BeanFactoryXmlImpl($filename);
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
        parent::__construct();
        $this->_beanDefs = array();
        $this->_filename = $filename;
        $this->_load();
    }
}

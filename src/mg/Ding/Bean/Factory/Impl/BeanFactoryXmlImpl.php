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
            : $this->_loadBean($beanName);
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
        $atts = $simpleXmlAspect->attributes();
        $aspectBean = (string)$atts->ref;
        $type = (string)$atts->type;
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
        $propName = (string)$simpleXmlProperty->attributes()->name;
        if (isset($simpleXmlProperty->ref)) {
            $propType = BeanPropertyDefinition::PROPERTY_BEAN;
            $propValue = (string)$simpleXmlProperty->ref->attributes()->bean;  
        } else if (isset($simpleXmlProperty->array)) {
            $propType = BeanPropertyDefinition::PROPERTY_ARRAY;
            $propValue = array();
            foreach ($simpleXmlProperty->array->entry as $arrayEntry) {
                $key = (string)$arrayEntry->attributes()->key;
                $propValue[$key] = $this->_loadProperty($arrayEntry);
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
    private function _loadBean($beanName)
    {
        $simpleXmlBean = $this->_simpleXml->xpath("//bean[@id='$beanName']");
        if (false === $simpleXmlBean) {
            return false;
        }
        // asume valid xml (only one bean with that id)
        $simpleXmlBean = $simpleXmlBean[0];

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
        if (isset($simpleXmlBean->attributes()->{'init-method'})) {
            $bInitMethod = (string)$simpleXmlBean->attributes()->{'init-method'};
        } else {
            $bInitMethod = false;
        }
        if (isset($simpleXmlBean->attributes()->{'destroy-method'})) {
            $bDestroyMethod = (string)$simpleXmlBean->attributes()->{'destroy-method'};
        } else {
            $bDestroyMethod = false;
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
            $bInitMethod, $bDestroyMethod, $bProps, $bAspects, $constructorArgs
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
    }

    /**
     * Returns a instance for this factory.
     * 
     * @param string $filename   beans.xml path.
     * @param array  $properties container properties.
     *
     * @return BeanFactoryXmlImpl
     */
    public static function getInstance($filename, array $properties = array())
    {
        if (self::$_instance == false) {
            self::$_instance = new BeanFactoryXmlImpl($filename, $properties);
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
    protected function __construct($filename, $properties)
    {
        parent::__construct($properties);
        $this->_beanDefs = array();
        $this->_filename = $filename;
        $this->_load();
    }
}

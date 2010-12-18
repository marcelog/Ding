<?php
/**
 * XML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Component
 * @subpackage Factory.Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Component\Factory\Impl;

use Ding\Component\Factory\BeanFactory;
use Ding\Component\BeanDefinition;
use Ding\Component\BeanPropertyDefinition;
use Ding\Aspect\AspectDefinition;

/**
 * XML bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Component
 * @subpackage Factory.Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class BeanFactoryXmlImpl extends BeanFactory
{
    private $_beanDefs;
    private $_filename;
    private $_simpleXml;
    
    public function getBeanDefinition($beanName)
    {
        return
            isset($this->_beanDefs[$beanName])
            ? $this->_beanDefs[$beanName]
            : false
        ;
    }
    
    private function _getXmlErrors()
    {
        $errors = '';
        foreach (libxml_get_errors() as $error) {
            $errors .= $error->message . "\n";
        }
        return $errors;
    }

    private function _loadXml($filename)
    {
        libxml_use_internal_errors(true);
        if (!file_exists($filename)) {
            throw new BeanFactoryException($filename . ' not found.');
        }
        return simplexml_load_string(file_get_contents($filename));
    }
    
    private function _loadAspect($simpleXmlAspect)
    {
        $aspects = array();
        $aspectBean = (string)$simpleXmlAspect->attributes()->ref;
        $type = (string)$simpleXmlAspect->attributes()->type;
        if ($type == 'before') {
            $type = AspectDefinition::ASPECT_BEFORE;
        } else if ($type == 'after') {
            $type = AspectDefinition::ASPECT_AFTER;
        } else if ($type == 'afterThrowing') {
            $type = AspectDefinition::ASPECT_AFTERTHROW;
        } else if ($type == 'afterFinally') {
            $type = AspectDefinition::ASPECT_AFTERFINALLY;
        } else if ($type == 'around') {
            $type = AspectDefinition::ASPECT_AROUND;
        } else {
            throw new BeanFactoryException('Invalid aspect type');
        }
        foreach ($simpleXmlAspect->pointcut as $pointcut) {
            $aspect = new AspectDefinition(
                (string)$pointcut->attributes()->expression,
                (string)$pointcut->attributes()->advice,
                intval($type), 
                $aspectBean 
            );
        }
        return $aspect;
    }
    
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
    
    private function _loadBean($simpleXmlBean)
    {
        $bName = (string)$simpleXmlBean->attributes()->id;
        $bClass = (string)$simpleXmlBean->attributes()->class;
        $bScope = (string)$simpleXmlBean->attributes()->scope;
        if ($bScope == 'prototype') {
            $bScope = BeanDefinition::BEAN_PROTOTYPE;
        } else if ($bScope == 'singleton') {
            $bScope = BeanDefinition::BEAN_SINGLETON;
        } else {
            throw new BeanFactoryException('Invalid bean scope: ' . $bScope);
        }
        $bProps = array();
        $bAspects = array();
        foreach ($simpleXmlBean->property as $property) {
            $bProps[] = $this->_loadProperty($property);
        }
        foreach ($simpleXmlBean->aspect as $aspect) {
            $bAspects[] = $this->_loadAspect($aspect);
        }
        return new BeanDefinition($bName, $bClass, $bScope, $bProps, $bAspects);
    }
    
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

    public function __construct($filename)
    {
        parent::__construct();
        $this->_beanDefs = array();
        $this->_filename = $filename;
        $this->_load();
    }
}

<?php
/**
 * This class is a primitive kind of BeanFactoryXml and will go away soon.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Component
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Component;
use Ding\Aspect\AspectDefinition;

/**
 * This class is a primitive kind of BeanFactoryXml and will go away soon.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Component
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class BeanList
{
    private $_beans;
    private $_filename;
    private $_simpleXml;
    
    public function getBean($bean)
    {
        return isset($this->_beans[$bean]) ? $this->_beans[$bean] : false;
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
            throw new BeanListException($filename . ' not found.');
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
            throw new BeanListException('Invalid aspect type');
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
            throw new BeanListException('Invalid bean scope: ' . $bScope);
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
    
    public function load()
    {
        $this->_simpleXml = $this->_loadXml($this->_filename);
        if (!$this->_simpleXml) {
            throw new BeanListException(
                'Could not parse: ' . $this->_filename
                . ': ' . $this->_getXmlErrors()
            );
        }
        $this->_beans = array();
        foreach ($this->_simpleXml->bean as $bean) {
            $newBean = $this->_loadBean($bean);
            $this->_beans[$newBean->getName()] = $newBean;         
        }
    }

    public function __construct($filename)
    {
        $this->_beans = array();
        $this->_filename = $filename;
    }
}

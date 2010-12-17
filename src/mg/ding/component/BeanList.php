<?php
namespace Ding;

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
        foreach ($simpleXmlAspect->pointcut as $pointcut) {
            $aspect = new AspectDefinition(
                (string)$pointcut->attributes()->expression,
                (string)$pointcut->attributes()->advice,
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

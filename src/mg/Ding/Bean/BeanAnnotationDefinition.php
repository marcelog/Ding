<?php
namespace Ding\Bean;

abstract class BeanAnnotationDefinition
{
    private $_name;
    private $_args;
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function getArguments()
    {
        return $this->_args;
    }
    
    public function __construct($name, $args)
    {
        $this->_name = $name;
        $this->_args = $args;
    }
}
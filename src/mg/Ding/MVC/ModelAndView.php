<?php
namespace Ding\MVC;

class ModelAndView
{
    private $_objects;
    private $_name;
    
    /**
     * @param unknown_type $objects
     * 
     * @return void
     */
    public function add(array $objects)
    {
        foreach ($objects as $name => $value) {
            $this->_objects[$name] = $value;
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getModel()
    {
        return $this->_objects;
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function __construct($name, array $options = array())
    {
        $this->_objects = $options;
        $this->_name = $name;
    }
}
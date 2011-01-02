<?php
namespace Ding\MVC;

interface IAction
{
    private $_id;
    private $_arguments;
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getArguments()
    {
        return $this->_arguments;
    }
    
    protected function __construct($id, array $arguments = array())
    {
        $this->_id = $id;
        $this->_arguments = $arguments;
    }
} 
<?php
namespace Ding\MVC;

use Ding\MVC\Exception\MVCException;

abstract class Dispatcher
{
    /**
     * Known controllers. 
     * @var Controller[]
     */
    private $_controllers;
    
    /**
     * @var IViewResolver
     */
    private $_viewResolver;
    
    /**
     * @var IMapper
     */
    private $_mapper;
    
    public function getControllers()
    {
        return $this->_controllers;
    }
    
    public function dispatch(Action $action)
    {
        $mapper = $this->_mapper;
        $viewResolver = $this->_viewResolver;
        $dispatchInfo = $mapper->map($action);
        
        if ($dispatchInfo === false) {
            throw new MVCException(
            	'No suitable controller for: ' . $action->getId()
            );
        }
        
        $controller = $dispatchInfo[0];
        $actionHandler = $dispatchInfo[1];
        if (!method_exists($controller, $actionHandler)) {
            throw new MVCException('No valid action handler found');
        }
        $controller->$actionHandler($action->getArguments());
    }
    
    public function setControllers($controllers)
    {
        $this->_controllers = $controllers;
    }

    public function setViewResolver(IViewResolver $viewResolver)
    {
        $this->_viewResolver = $viewResolver;
    }

    public function setMapper(IMapper $mapper)
    {
        $this->_mapper = $mapper;
    }
    
    public function getMapper()
    {
        return $this->_mapper;
    }
    
    public function getViewResolver()
    {
        return $this->_viewResolver;
    }
    
    public function __construct()
    {
    }
}

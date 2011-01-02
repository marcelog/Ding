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
    
    public function getControllers()
    {
        return $this->_controllers;
    }
    
    protected function findSuitable(IAction $action)
    {
        $actionId = $action->getId();
        foreach ($controllers as $controller) {
            $mappings = $controller->getMappings();
            foreach ($mappings as $name => $value) {
                if ($name === $actionId) {
                    return array($controller, $value);
                }
            }
        }
        return false;
    }
    
    public abstract function dispatch(IAction $action);

    public function setControllers($controllers)
    {
        $this->_controllers = $controllers;
    }

    public function setViewResolver(IViewResolver $viewResolver)
    {
        $this->_viewResolver = $viewResolver;
    }
    
    public function getViewResolver()
    {
        return $this->_viewResolver;
    }
    
    public function __construct()
    {
    }
}

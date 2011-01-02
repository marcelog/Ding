<?php
namespace Ding\MVC\Http;

use Ding\MVC\Dispatcher;
use Ding\MVC\IAction;

class HttpDispatcher extends Dispatcher
{
    private $_get;
    private $_post;
    private $_server;
    private $_cookies;
    private $_session;

    public function init()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_server = $_SERVER;
        $this->_cookies = $_COOKIE;
    }

    public function dispatch(IAction $action)
    {
        $dispatchInfo = $this->_findSuitable($action, $controllers);
        if ($controller === false) {
            throw new MVCException(
            	'No suitable controller for: ' . $action->getId()
            );
        }
        $controller = $dispatchInfo[0];
        $actionHandler = $dispatchInfo[1];
        $controller->$actionHandler($action->getArguments());
    }
    
    public function __construct()
    {
    }
}
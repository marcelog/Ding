<?php
namespace Ding\MVC\Http;

use Ding\MVC\ModelAndView;

use Ding\Container\Impl\ContainerImpl;

class HttpFrontController
{
    private static $_properties;
    
    public function handle()
    {
        try
        {
            $container = ContainerImpl::getInstance(self::$_properties);
            $dispatcher = $container->getBean('HttpDispatcher');
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $url = $_SERVER['REQUEST_URI'];
            $arguments = array();
            $arguments[$method] = $arguments; // just reusing an empty array
            $vars = $arguments; // idem
            if ($method === 'GET') {
                $vars = $_GET;
            } else if ($method === 'POST') {
                $vars = $_POST;
            }                
            foreach ($_GET as $k => $v) {
                $arguments[$method][$k] = $v;
            }
            $action = new HttpAction($url, $arguments);
            $action->setMethod($method);
            $modelAndView = $dispatcher->dispatch($action);
            if (!($modelAndView instanceof ModelAndView)) {
                $modelAndView = new ModelAndView('Main');
            }
        } catch(Exception $exception) {
            header('HTTP/1.1 500 Not Found');
        }
    }
    
    public static function configure($properties)
    {
        self::$_properties = $properties;
    }

    public function __construct()
    {
    }
}

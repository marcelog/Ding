<?php
namespace Ding\MVC\Http;

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
            $action = new HttpAction($id, $arguments);
        } catch(Exception $exception) {
            echo $exception . "\n";
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

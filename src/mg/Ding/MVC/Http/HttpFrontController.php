<?php
/**
 * Http front controller.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\MVC\Http;

use Ding\MVC\ModelAndView;
use Ding\Container\Impl\ContainerImpl;

/**
 * Http front controller.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class HttpFrontController
{
    /**
     * Container properties.
     * @var array
     */
    private static $_properties;

    /**
     * Handles the request. This will instantiate the container with the given
     * properties (via static method configure(), see below). Then it will
     * getBean(HttpDispatcher) and call dispatch() on it with an Action created
     * based on the request uri and method parameters (get, post, etc).
     *
     * @return void
     */
    public function handle()
    {
        ob_start();
        $exceptionMapper = false;
        try
        {
            $container = ContainerImpl::getInstance(self::$_properties);
            $dispatcher = $container->getBean('HttpDispatcher');
            $exceptionMapper = $container->getBean('HttpExceptionMapper');
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $url = $_SERVER['REQUEST_URI'];
            $argsStart = strpos($url, '?');
            if ($argsStart != false) {
                $urlArgs = substr($url, $argsStart + 1);
                $arguments = explode('&', $urlArgs);
                $variables = array();
                foreach ($arguments as $argument) {
                    $data = explode('=', $argument);
                    $variables[$data[0]] = isset($data[1]) ? $data[1] : '';
                }
            } else {
                $variables = array();
            }
            $action = new HttpAction($url, $variables);
            $action->setMethod($method);
            $dispatcher->dispatch($action);
        } catch(\Exception $exception) {
            ob_end_clean();
            ob_start();
            if ($exceptionMapper === false) {
                header('HTTP/1.1 500 Error.');
            } else {
                $action = new HttpAction(
                    get_class($exception), array('exception' => $exception)
                );
                $dispatcher->dispatch($action, $exceptionMapper);
            }
        }
        ob_end_flush();
    }

    /**
     * Configures this frontcontroller with the container properties.
     *
     * @param array $properties Container properties.
     *
     * @return void
     */
    public static function configure(array $properties)
    {
        self::$_properties = $properties;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }
}

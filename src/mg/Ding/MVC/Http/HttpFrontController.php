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
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Ding\MVC\Http;

use Ding\MVC\IMapper;
use Ding\MVC\Exception\MVCException;
use Ding\MVC\ModelAndView;
use Ding\MVC\RedirectModelAndView;
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
     * Log4PHP Logger or own dummy implementation.
     * @var Logger
     */
    private static $_logger;

    /**
     * Cached isDebugEnabled() from Logger.
     * @var boolean
     */
    private static $_loggerDebugEnabled;

    public static function dispatch(
        HttpDispatcher $dispatcher,
        HttpViewResolver $viewResolver,
        HttpAction $action,
        IMapper $mapper
    ) {
        $modelAndView = $dispatcher->dispatch($action, $mapper);
        if ($modelAndView instanceof RedirectModelAndView) {
            if (self::$_loggerDebugEnabled) {
                self::$_logger->debug(
                	'Forwarding ModelAndView: ' . $modelAndView->getName()
                );
            }
            $newAction = new HttpAction($modelAndView->getName(), $modelAndView->getModel());
            $newAction->getMethod($action->getMethod());
            self::dispatch($dispatcher, $viewResolver, $newAction, $mapper);
        } else if ($modelAndView instanceof ModelAndView) {
            if (self::$_loggerDebugEnabled) {
                self::$_logger->debug(
                	'Using ModelAndView: ' . $modelAndView->getName()
                );
            }
            $view = $viewResolver->resolve($modelAndView);
            $view->render();
        } else {
            if (self::$_loggerDebugEnabled) {
                self::$_logger->debug('Using default action Main');
            }
            $modelAndView = new ModelAndView('Main');
        }
    }
    /**
     * Handles the request. This will instantiate the container with the given
     * properties (via static method configure(), see below). Then it will
     * getBean(HttpDispatcher) and call dispatch() on it with an Action created
     * based on the request uri and method parameters (get, post, etc).
     *
     * @return void
     */
    public static function handle(array $properties = array(), $baseUrl = '/')
    {
        $container = ContainerImpl::getInstance($properties);
        self::$_logger = \Logger::getLogger('Ding.MVC');
        self::$_loggerDebugEnabled = self::$_logger->isDebugEnabled();
        $baseUrlLen = strlen($baseUrl);
        session_start();
        ob_start();
        $exceptionMapper = $dispatcher = $viewResolver = false;
        try
        {
            $dispatcher = $container->getBean('HttpDispatcher');
            $viewResolver = $container->getBean('HttpViewResolver');
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $url = $_SERVER['REQUEST_URI'];
            $urlStart = strpos($url, $baseUrl);

            if (self::$_loggerDebugEnabled) {
                self::$_logger->debug('Trying to match: ' . $url);
            }
            if ($urlStart === false || $urlStart > 0) {
                throw new MVCException($url . ' is not a base url.');
            }
            $url = substr($url, $baseUrlLen);
            $variables = array();
            if ($method == 'get') {
                $argsStart = strpos($url, '?');
                if ($argsStart != false) {
                    $urlArgs = substr($url, $argsStart + 1);
                    $arguments = explode('&', $urlArgs);
                    $variables = array();
                    foreach ($arguments as $argument) {
                        $data = explode('=', $argument);
                        $variables[$data[0]] = isset($data[1]) ? $data[1] : '';
                    }
                }
            } else if ($method == 'post') {
                $variables = $_POST;
            }
            $action = new HttpAction($url, $variables);
            $action->setMethod($method);
            $mapper = $container->getBean('HttpUrlMapper');
            self::dispatch($dispatcher, $viewResolver, $action, $mapper);
        } catch(\Exception $exception) {
            $exceptionMapper = $container->getBean('HttpExceptionMapper');
            if (self::$_loggerDebugEnabled) {
                self::$_logger->debug('Got Exception: ' . $exception);
            }
            ob_end_clean();
            ob_start();
            if ($exceptionMapper === false) {
                header('HTTP/1.1 500 Error.');
            } else {
                $action = new HttpAction(
                    get_class($exception), array('exception' => $exception)
                );
                self::dispatch($dispatcher, $viewResolver, $action, $exceptionMapper);
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
    private function __construct($baseUrl = '/')
    {
    }
}

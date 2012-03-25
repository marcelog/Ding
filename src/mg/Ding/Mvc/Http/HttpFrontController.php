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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
namespace Ding\Mvc\Http;

use Ding\Mvc\IViewRender;
use Ding\HttpSession\HttpSession;
use Ding\Mvc\IMapper;
use Ding\Mvc\Exception\MvcException;
use Ding\Mvc\ModelAndView;
use Ding\Mvc\RedirectModelAndView;
use Ding\Mvc\ForwardModelAndView;
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class HttpFrontController
{
    /**
     * Log4PHP Logger or own dummy implementation.
     * @var Logger
     */
    private static $_logger;

    public static function dispatch(
        HttpDispatcher $dispatcher,
        HttpViewResolver $viewResolver,
        HttpAction $action,
        IMapper $mapper,
        IViewRender $render
    ) {
        $dispatchInfo = $mapper->map($action);
        if ($dispatchInfo === false) {
            throw new MvcException(
            	'No suitable controller for: ' . $action->getId()
            );
        }
        $controller = $dispatchInfo->handler;
        $actionHandler = $dispatchInfo->method;

        self::$_logger->debug(
        	'Found mapped controller: ' . get_class($controller)
            . ' with action: ' . $actionHandler
        );
        $modelAndView = $dispatcher->dispatch($dispatchInfo);
        if ($modelAndView instanceof ForwardModelAndView) {
            self::$_logger->debug(
              	'Forwarding ModelAndView: ' . $modelAndView->getName()
            );
            $newAction = new HttpAction($modelAndView->getName(), $modelAndView->getModel());
            $newAction->getMethod($action->getMethod());
            self::dispatch($dispatcher, $viewResolver, $newAction, $mapper, $render);
        } else if ($modelAndView instanceof RedirectModelAndView) {
            header('HTTP/1.1 302 Moved');
            $location = $modelAndView->getName();
            if (!$modelAndView->isEmpty()) {
                $location .= '?' . http_build_query($modelAndView->getModel());
            }
            header('Location: ' . $location);
            self::$_logger->debug(
               	'Redirecting ModelAndView: ' . $modelAndView->getName()
                . " -> $location"
            );
        } else if ($modelAndView instanceof ModelAndView) {
            self::$_logger->debug(
               	'Using ModelAndView: ' . $modelAndView->getName()
            );
            $view = $viewResolver->resolve($modelAndView);
            $render->render($view);
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
        $exceptionThrown = null;
        $filtersPassed = true;
        $session = HttpSession::getSession();
        $container = ContainerImpl::getInstance($properties);
        self::$_logger = \Logger::getLogger(__CLASS__);
        $baseUrlLen = strlen($baseUrl);
        ob_start();
        $exceptionMapper = $render = $dispatcher = $viewResolver = false;
        $interceptors = array();
        try
        {
            $dispatcher = $container->getBean('HttpDispatcher');
            $viewResolver = $container->getBean('HttpViewResolver');
            $exceptionMapper = $container->getBean('HttpExceptionMapper');
            $render = $container->getBean('HttpViewRender');
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $url = $_SERVER['REQUEST_URI'];
            $urlStart = strpos($url, $baseUrl);

            self::$_logger->debug('Trying to match: ' . $url);
            if ($urlStart === false || $urlStart > 0) {
                throw new MvcException($url . ' is not a base url.');
            }
            $url = substr($url, $baseUrlLen);
            $variables = array();
            if (!empty($_GET)) {
                $variables = array_merge($variables, $_GET);
            }
            if (!empty($_POST)) {
                $variables = array_merge($variables, $_POST);
            }
            $action = new HttpAction($url, $variables);
            $action->setMethod($method);
            $mapper = $container->getBean('HttpUrlMapper');
            self::dispatch($dispatcher, $viewResolver, $action, $mapper, $render);
        } catch(\Exception $exception) {
            $exceptionThrown = $exception;
            self::$_logger->debug('Got Exception: ' . $exception);
            ob_end_clean();
            ob_start();
            if ($exceptionMapper === false) {
                header('HTTP/1.1 500 Error.');
            } else {
                $action = new HttpAction(
                    get_class($exception), array('exception' => $exception)
                );
                self::dispatch($dispatcher, $viewResolver, $action, $exceptionMapper, $render);
            }
        }
        ob_end_flush();
    }

}

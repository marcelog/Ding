<?php
/**
 * Generic dispatcher.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
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
namespace Ding\Mvc;

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Reflection\IReflectionFactory;
use Ding\Logger\ILoggerAware;
use Ding\Mvc\Exception\MvcException;
use Ding\Mvc\DispatchInfo;

/**
 * Generic dispatcher.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
abstract class Dispatcher implements ILoggerAware, IReflectionFactoryAware
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * @var IHandlerInterceptor[]
     */
    private $_interceptors = array();

    /**
     * Main action. Will use the action mapper to get a controller that can
     * handle the given Action, and then the viewresolver to get a View that
     * can render the returned ModelAndView from the controller.
     *
     * @param Action  $action Action to dispatch.
     *
     * @throws MvcException
     * @return void
     */
    public function dispatch(DispatchInfo $dispatchInfo)
    {
        $controller = $dispatchInfo->handler;
        $actionHandler = $dispatchInfo->method;
        $action = $dispatchInfo->action;
        if (!method_exists($controller, $actionHandler)) {
            throw new MvcException('No valid action handler found: ' . $actionHandler);
        }
        $interceptors = $dispatchInfo->interceptors;
        $filtersPassed = true;
        $result = false;
        foreach ($interceptors as $interceptor) {
            $this->_logger->debug("Running pre filter: " . get_class($interceptor));
            $result = $interceptor->preHandle($action, $controller);
            if ($result === false) {
                $filtersPassed = false;
                $this->_logger->debug("Filter returned false, stopping dispatch");
                break;
            } else if ($result instanceof ModelAndView) {
                return $result;
            }
        }
        if ($filtersPassed) {
            $result = $this->invokeAction($controller, $actionHandler, $action->getArguments());
            foreach ($interceptors as $interceptor) {
                $this->_logger->debug("Running post filter: " . get_class($interceptor));
                $interceptor->postHandle($action, $controller);
            }
        }
        return $result;
    }

	/**
     * Calls the specified method from the specifed object using the specified
     * arguments map.
     *
     * @param object $object
     * @param string $method Method name
     * @param array $arguments Map of arguments, where key is argument name,
     * and value is argument value.
     *
     * @return mxied
     */
    private function invokeAction($object, $method, array $arguments)
    {
        $methodInfo = $this->reflectionFactory->getMethod(
            get_class($object), $method
        );
        $parameters = $methodInfo->getParameters();
        $values = array();
        $total = count($parameters);
        for ($i = 0; $i < $total; $i++) {
            $parameter = array_shift($parameters);
            $name = $parameter->getName();
            if (isset($arguments[$name])) {
                $values[] = $arguments[$name];
            } else if ($parameter->isOptional()) {
                $values[] = $parameter->getDefaultValue();
            } else {
                $ctl = get_class($object);
                throw new MvcException(
                	"Missing required argument: $name for action $ctl:$method"
                );
            }
        }
        return $methodInfo->invokeArgs($object, $values);
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Logger.ILoggerAware::setLogger()
     */
    public function setLogger(\Logger $logger)
    {
        $this->_logger = $logger;
    }
}

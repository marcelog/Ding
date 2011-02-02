<?php
/**
 * Generic dispatcher.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
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
namespace Ding\MVC;

use Ding\MVC\Exception\MVCException;

/**
 * Generic dispatcher.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
abstract class Dispatcher
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Cache for isDebugEnabled()
     * @var boolean
     */
    private $_loggerDebugEnabled;

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

    /**
     * @var IMapper
     */
    private $_exceptionMapper;

    /**
     * Sets controller.
     *
     * @param Controller[] $controllers Controllers.
     *
     * @return void
     */
    public function setControllers($controllers)
    {
        $this->_controllers = $controllers;
    }

    /**
     * Returns all controllers this dispatcher knows about.
     *
     * @return Controller[]
     */
    public function getControllers()
    {
        return $this->_controllers;
    }

    /**
     * Main action. Will use the action mapper to get a controller that can
     * handle the given Action, and then the viewresolver to get a View that
     * can render the returned ModelAndView from the controller.
     *
     * @param Action  $action Action to dispatch.
     * @param IMapper $mapper Optional mapper. Uses default if none specified.
     *
     * @throws MVCException
     * @return void
     */
    public function dispatch(Action $action, $mapper = false)
    {
        if ($mapper === false) {
            $mapper = $this->_mapper;
        }
        $viewResolver = $this->_viewResolver;
        $dispatchInfo = $mapper->map($action);
        if ($dispatchInfo === false) {
            throw new MVCException(
            	'No suitable controller for: ' . $action->getId()
            );
        }

        $controller = $dispatchInfo[0];
        $actionHandler = $dispatchInfo[1];
        if ($this->_loggerDebugEnabled) {
            $this->_logger->debug(
            	'Found mapped controller: '
                . get_class($controller)
                . ' with action: '
                . $actionHandler
            );
        }
        if (!method_exists($controller, $actionHandler)) {
            throw new MVCException('No valid action handler found: ' . $actionHandler);
        }
        $modelAndView = $controller->$actionHandler($action->getArguments());
        if (!($modelAndView instanceof ModelAndView)) {
            $modelAndView = new ModelAndView('Main');
        }
        if ($this->_loggerDebugEnabled) {
            $this->_logger->debug(
            	'Using ModelAndView: ' . $modelAndView->getName()
            );
        }
        $view = $viewResolver->resolve($modelAndView);
        $view->render();
    }

    /**
     * Sets an action mapper.
     *
     * @param IMapper $mapper New action mapper to use.
     *
     * @return void
     */
    public function setMapper(IMapper $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Returns action mapper.
     *
     * @return IMapper
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * Sets a view resolver.
     *
     * @param IViewResolver $viewResolver New view resolver to use.
     *
     * @return void
     */
    public function setViewResolver(IViewResolver $viewResolver)
    {
        $this->_viewResolver = $viewResolver;
    }

    /**
     * Returns view resolver.
     *
     * @return IViewResolver
     */
    public function getViewResolver()
    {
        return $this->_viewResolver;
    }

    /**
     * Constructor. Nothing to see here, move along.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = \Logger::getLogger('Ding.MVC');
        $this->_loggerDebugEnabled = $this->_logger->isDebugEnabled();
    }
}

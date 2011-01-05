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
     * @param Action $action Action to dispatch.
     *
     * @throws MVCException
     * @return void
     */
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
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug(
            	'Found mapped controller: '
                . get_class($controller)
                . ' with action: '
                . $actionHandler
            );
        }
        if (!method_exists($controller, $actionHandler)) {
            throw new MVCException('No valid action handler found');
        }
        $modelAndView = $controller->$actionHandler($action->getArguments());
        if (!($modelAndView instanceof ModelAndView)) {
            $modelAndView = new ModelAndView('Main');
        }
        if ($this->_logger->isDebugEnabled()) {
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
    }
}

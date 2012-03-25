<?php
/**
 * An exception mapper implementation for http requests.
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

use Ding\Logger\ILoggerAware;
use Ding\Mvc\IMapper;
use Ding\Mvc\Action;
use Ding\Mvc\DispatchInfo;

/**
 * An exception mapper implementation for http requests.
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
class HttpExceptionMapper implements IMapper, ILoggerAware
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * @var Controller[]
     */
    private $_map;

    /**
     * Sets the map for this mapper.
     *
     * @param array[] $map An array containing arrays defined like this:
     * [0] => IAction, [1] => IController
     *
     * (non-PHPdoc)
     * @see Ding\Mvc.IMapper::setMap()
     *
     * @return void
     */
    public function setMap(array $map)
    {
        $this->_map = $map;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Logger.ILoggerAware::setLogger()
     */
    public function setLogger(\Logger $logger)
    {
        $this->_logger = $logger;
    }
    /**
     * This will map an exception action to a controller. Will look for
     * an instance of the mapped exception to the thrown exception.
     *
     * @param Action $action Original action (exception).
     *
     * @return array [0] => Controller [1] => Method to call (With
     * 'Exception' appended to the end of the method name).
     */
    public function map(Action $action)
    {
        $exception = $action->getArguments();
        $exception = $exception['exception'];

        $this->_logger->debug('Exception mapper invoked with: ' . $action->getId());

        // Lookup a controller that can handle this url.
        foreach ($this->_map as $map) {
            $controllerException = $map[0];
            $controller = $map[1];
            if (!($exception instanceof $controllerException)) {
                continue;
            }
            return new DispatchInfo(
                $action, $controller,
                str_replace('\\', '_', $controllerException) . 'Exception'
            );
        }
        return false;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_map = array();
        $this->_baseUrl = '/';
    }
}
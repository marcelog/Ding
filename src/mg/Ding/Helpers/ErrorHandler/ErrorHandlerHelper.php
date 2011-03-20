<?php
/**
 * This is a bean that will call your own error handler (only if it implements
 * IErrorHandler).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ErrorHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
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
namespace Ding\Helpers\ErrorHandler;

/**
 * This is a bean that will call your own error handler (only if it implements
 * IErrorHandler).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ErrorHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class ErrorHandlerHelper
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Error handler to call.
     * @var IErrorHandler
     */
    private $_handler;

    /**
     * Set a handler to call upon errors.
     *
     * @param IErrorHandler $handler Handler to call.
     *
     * @return void
     */
    public function setErrorHandler(IErrorHandler $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * This was set by set_error_handler. Returns true.
     *
     * @param integer $errno   PHP Error type.
     * @param string  $errstr  Error message.
     * @param string  $errfile File that triggered the error.
     * @param integer $errline Line that triggered the error.
     *
     * @return boolean
     */
    public function handle($errno, $errstr, $errfile, $errline)
    {
        $info = new ErrorInfo($errno, $errstr, $errfile, $errline);
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug(
                implode(' | ', array($errno, $errstr, $errfile, $errline))
            );
        }
        $this->_handler->handleError($info);
        return true;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = \Logger::getLogger('Ding.ErrorHandlerHelper');
    }
}
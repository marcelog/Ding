<?php
/**
 * This class carries information from the error helper to your own error
 * handler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ErrorHandler
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
namespace Ding\Helpers\ErrorHandler;

/**
 * This class carries information from the error helper to your own error
 * handler.
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
class ErrorInfo
{
    /**
     * PHP Type of error (E_NOTICE).
     * @return integer
     */
    private $_type;

    /**
     * Error message.
     * @var string
     */
    private $_message;

    /**
     * File that triggered the error.
     * @var string
     */
    private $_file;

    /**
     * Line that triggered the error.
     * @return integer
     */
    private $_line;

    /**
     * Returns PHP Error type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns error message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Returns the file that triggered the error.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Returns line that triggered the error.
     *
     * @return integer
     */
    public function getLine()
    {
        return $this->_line;
    }

    /**
     * Returns a human readable string description of the given error type.
     *
     * @param integer $type Error type to convert to string, from getType()
     *
     * @return string
     */
    public static function typeToString($type)
    {
        switch($type)
        {
        case E_USER_ERROR:
            return 'User Error';
        case E_USER_WARNING:
            return 'User Warning';
        case E_USER_NOTICE:
            return 'User Notice';
        case E_USER_DEPRECATED:
            return 'User deprecated';
        case E_DEPRECATED:
            return 'Deprecated';
        case E_RECOVERABLE_ERROR:
            return 'Recoverable error';
        case E_STRICT:
            return 'Strict';
        case E_WARNING:
            return 'Warning';
        case E_NOTICE:
            return 'Notice';
        case E_ERROR:
            return 'Error';
        default:
            return 'Unknown';
        }
    }

    /**
     * Standard.
     *
     * @return string
     */
    public function __toString()
    {
        return
            '[ ErrorInfo: '
            . ' type: ' . self::typeToString($this->getType())
            . ', Message: ' . $this->getMessage()
            . ', File: ' . $this->getFile()
            . ', Line: ' . $this->getLine()
            . ']'
        ;
    }
    /**
     * Constructor.
     *
     * @param integer $type    PHP Error type (E_NOTICE, E_USER_*, etc).
     * @param string  $message Error message.
     * @param string  $file    File that triggered the error.
     * @param integer $line    Line that triggered the error.
     *
     * @return void
     */
    public function __construct($type, $message, $file, $line)
    {
        $this->_type = $type;
        $this->_message = $message;
        $this->_file = $file;
        $this->_line = $line;
    }
}
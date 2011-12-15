<?php
/**
 * This class will test the error handler driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Error
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

use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\ErrorHandler\ErrorInfo;

/**
 * This class will test the error handler driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Error
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Error extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_set_error_handler()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array('filename' => 'errorBeans.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $line = __LINE__; trigger_error("an error", E_USER_NOTICE);
        $this->assertTrue(MyErrorHandler::$handled);
        $this->assertEquals(MyErrorHandler::$error->getType(), E_USER_NOTICE);
        $this->assertEquals(MyErrorHandler::$error->getMessage(), "an error");
        $this->assertEquals(MyErrorHandler::$error->getFile(), __FILE__);
        $this->assertEquals(MyErrorHandler::$error->getLine(), $line);
        $this->assertEquals(
            '[ ErrorInfo:  type: '
            . ErrorInfo::typeToString(MyErrorHandler::$error->getType())
            . ', Message: an error'
            . ', File: ' . __FILE__
            . ', Line: ' . $line
            . ']',
            MyErrorHandler::$error->__toString()
        );
    }

    /**
     * @test
     */
    public function can_set_annotated_error_handler()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(__DIR__))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $line = __LINE__; trigger_error("an error", E_USER_NOTICE);
        $this->assertTrue(MyErrorHandler2::$handled);
        $this->assertEquals(MyErrorHandler2::$error->getType(), E_USER_NOTICE);
        $this->assertEquals(MyErrorHandler2::$error->getMessage(), "an error");
        $this->assertEquals(MyErrorHandler2::$error->getFile(), __FILE__);
        $this->assertEquals(MyErrorHandler2::$error->getLine(), $line);
        $this->assertEquals(
            '[ ErrorInfo:  type: '
            . ErrorInfo::typeToString(MyErrorHandler2::$error->getType())
            . ', Message: an error'
            . ', File: ' . __FILE__
            . ', Line: ' . $line
            . ']',
            MyErrorHandler2::$error->__toString()
        );
    }

    /**
     * @test
     */
    public function can_do_nothing_if_no_handlers()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
    }

    /**
     * @test
     */
    public function can_discriminate_errors()
    {
        $this->assertEquals(ErrorInfo::typeToString(E_USER_ERROR), "User Error");
        $this->assertEquals(ErrorInfo::typeToString(E_USER_WARNING), "User Warning");
        $this->assertEquals(ErrorInfo::typeToString(E_USER_NOTICE), "User Notice");
        $this->assertEquals(ErrorInfo::typeToString(E_USER_DEPRECATED), "User deprecated");
        $this->assertEquals(ErrorInfo::typeToString(E_DEPRECATED), "Deprecated");
        $this->assertEquals(ErrorInfo::typeToString(E_RECOVERABLE_ERROR), "Recoverable error");
        $this->assertEquals(ErrorInfo::typeToString(E_STRICT), "Strict");
        $this->assertEquals(ErrorInfo::typeToString(E_WARNING), "Warning");
        $this->assertEquals(ErrorInfo::typeToString(E_NOTICE), "Notice");
        $this->assertEquals(ErrorInfo::typeToString(E_ERROR), "Error");
        $this->assertEquals(ErrorInfo::typeToString(time()), "Unknown");
    }
}

class MyErrorHandler
{
    public static $handled = false;
    public static $error = false;

    public function onDingError(ErrorInfo $error)
    {
        self::$error = $error;
        self::$handled = true;
    }
}

/**
 * @Component
 * @ListensOn(value=dingError)
 */
class MyErrorHandler2
{
    public static $handled = false;
    public static $error = false;

    public function onDingError(ErrorInfo $error)
    {
        self::$error = $error;
        self::$handled = true;
    }
}

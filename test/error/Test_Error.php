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
use Ding\Helpers\ErrorHandler\IErrorHandler;
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
        			'drivers' => array('errorhandler' => array()),
                    'bdef' => array(
                        'xml' => array('filename' => 'errorBeans.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        trigger_error("an error", E_USER_NOTICE);
        $this->assertTrue(MyErrorHandler::$handled);
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
        			'drivers' => array('errorhandler' => array()),
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(__DIR__))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        trigger_error("an error", E_USER_NOTICE);
        $this->assertTrue(MyErrorHandler2::$handled);
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
        			'drivers' => array('errorhandler' => array()),
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
    }
}

class MyErrorHandler implements IErrorHandler
{
    public static $handled = false;

    public function handleError(ErrorInfo $error)
    {
        self::$handled = true;
    }
}

/**
 * @ErrorHandler
 */
class MyErrorHandler2 implements IErrorHandler
{
    public static $handled = false;

    public function handleError(ErrorInfo $error)
    {
        self::$handled = true;
    }
}

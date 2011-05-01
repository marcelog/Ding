<?php
/**
 * This class will test the http front controller.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage mvc
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
use Ding\MVC\RedirectModelAndView;
use Ding\MVC\ForwardModelAndView;
use Ding\MVC\Http\HttpFrontController;
use Ding\Container\Impl\ContainerImpl;
use Ding\MVC\ModelAndView;

if (!defined('OUTPUT_TEST')) {
    define('OUTPUT_TEST', false);
}
/**
 * This class will test the http front controller.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage mvc
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_HttpFrontController extends PHPUnit_Framework_TestCase//PHPUnit_Extensions_OutputTestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'properties' => array('prefix' => RESOURCES_DIR),
                    'bdef' => array(
                        'xml' => array('filename' => 'frontcontroller.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_accept_get_arguments()
    {
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/withArguments?arg1=value1&arg2=value2';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
        $this->assertEquals(AController2::$args['arg1'], 'value1');
        $this->assertEquals(AController2::$args['arg2'], 'value2');
    }

    /**
     * @test
     */
    public function can_accept_post_arguments()
    {
        global $_SERVER;
        global $_POST;
        $_POST['arg1'] = 'value1';
        $_POST['arg2'] = 'value2';
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/withArguments';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
        $this->assertEquals(AController2::$args['arg1'], 'value1');
        $this->assertEquals(AController2::$args['arg2'], 'value2');
    }

    /**
     * @test
     */
    public function can_forward()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('hi there, im a forwarded view');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/forward';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_redirect()
    {
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/redirect';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_dispatch_and_render_default_action()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('hi there. im the default action');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_dispatch_and_render()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('hi there');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/something';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_dispatch_and_render_exceptions()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('damn other exception');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/somethingOtherException';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     * @expectedException Ding\MVC\Exception\MVCException
     */
    public function cannot_handle_uncatched_exceptions()
    {
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/MyController/somethingWithException';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/');
    }

    /**
     * @test
     * @expectedException Ding\MVC\Exception\MVCException
     */
    public function cannot_handle_invalid_base_url()
    {
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/invalidBaseURL/MyController/something';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_handle_uncatched_exceptions_with_no_exception_mapper()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'properties' => array('prefix' => RESOURCES_DIR),
                    'bdef' => array(
                        'xml' => array('filename' => 'frontcontroller2.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/MyController/somethingWithException';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($properties, '/');
    }
}

class OtherException extends \Exception
{
}

class AController2
{
    public static $args;

    public function MainAction(array $arguments = array())
    {
        return new ModelAndView('index');
    }

    public function someOtherAction(array $arguments = array())
    {
        return new ModelAndView('fwd');
    }

    public function withArgumentsAction(array $arguments = array())
    {
        self::$args = $arguments;
        return new ModelAndView('fwd');
    }

    public function forwardAction(array $arguments = array())
    {
        return new ForwardModelAndView('/MyController/someOther');
    }

    public function redirectAction(array $arguments = array())
    {
     return new RedirectModelAndView('http://www.google.com');
    }

    public function somethingOtherExceptionAction(array $arguments = array())
    {
        throw new OtherException();
    }

    public function _OtherExceptionException(array $arguments = array())
    {
        return new ModelAndView('someOtherException');
    }
    public function _ExceptionException(array $arguments = array())
    {
        return new ModelAndView('someException');
    }
    public function somethingAction(array $arguments = array())
    {
        return new ModelAndView('some');
    }
}
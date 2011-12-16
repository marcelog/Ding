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
use Ding\Mvc\RedirectModelAndView;
use Ding\Mvc\ForwardModelAndView;
use Ding\Mvc\Http\HttpFrontController;
use Ding\Container\Impl\ContainerImpl;
use Ding\Mvc\ModelAndView;

if (!defined('OUTPUT_TEST')) {
    define('OUTPUT_TEST', true);
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
class Test_HttpFrontController extends PHPUnit_Extensions_OutputTestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'properties' => array(
                        'twig.debug' => false,
                        'twig.charset' => 'utf-8',
                        'twig.base_template_class' => 'Twig_Template',
                        'twig.cache' => '/tmp/Ding/twigcache',
                        'twig.auto_reload' => true,
                        'twig.strict_variables' => false,
                        'twig.autoescape' => 0,
                        'smarty.compile_dir' => '/tmp/Ding/smartycompile/',
                		'smarty.config_dir' => '/tmp/Ding/',
                    	'smarty.cache_dir' => '/tmp/Ding/smartycache/',
                		'smarty.debugging' => false,
                		'prefix' => RESOURCES_DIR
                    ),
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
        global $_GET;
        $_GET['arg1'] = 'value1';
        $_GET['arg2'] = 'value2';
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/withArguments?arg1=value1&arg2=value2';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
        $this->assertEquals(AController2::$arg1, 'value1');
        $this->assertEquals(AController2::$arg2, 'value2');
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
        $this->assertEquals(AController2::$arg1, 'value1');
        $this->assertEquals(AController2::$arg2, 'value2');
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
    public function can_dispatch_and_render_twig()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('hi there, im a twig view');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/somethingTwig';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        HttpFrontController::handle($this->_properties, '/MyBaseURL');
    }

    /**
     * @test
     */
    public function can_dispatch_and_render_smarty()
    {
        global $_SERVER;
        if (OUTPUT_TEST) {
            $this->expectOutputString('hi there, im a smarty view b');
        }
        $_SERVER['REQUEST_URI'] = '/MyBaseURL/MyController/somethingSmarty';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'properties' => array(
                        'smarty.compile_dir' => '/tmp/Ding/smartycompile/',
                		'smarty.config_dir' => '/tmp/Ding/',
                    	'smarty.cache_dir' => '/tmp/Ding/smartycache/',
                		'smarty.debugging' => false,
                		'prefix' => RESOURCES_DIR
                    ),
                    'bdef' => array(
                        'xml' => array('filename' => 'frontcontroller3.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        HttpFrontController::handle($properties, '/MyBaseURL');
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
     * @expectedException Ding\Mvc\Exception\MvcException
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
     * @expectedException Ding\Mvc\Exception\MvcException
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
    public static $arg1;
    public static $arg2;

    public function MainAction()
    {
        return new ModelAndView('index');
    }

    public function someOtherAction()
    {
        return new ModelAndView('fwd');
    }

    public function withArgumentsAction($arg1, $arg2)
    {
        self::$arg1 = $arg1;
        self::$arg2 = $arg2;
        return new ModelAndView('fwd');
    }

    public function forwardAction()
    {
        return new ForwardModelAndView('/MyController/someOther');
    }

    public function redirectAction()
    {
     return new RedirectModelAndView('http://www.google.com');
    }

    public function somethingOtherExceptionAction()
    {
        throw new OtherException();
    }

    public function _OtherExceptionException()
    {
        return new ModelAndView('someOtherException');
    }
    public function _ExceptionException()
    {
        return new ModelAndView('someException');
    }
    public function somethingTwigAction()
    {
        return new ModelAndView('someTwig');
    }
    public function somethingSmartyAction()
    {
        $model = new ModelAndView('someSmarty');
        $model->add(array('a' => 'b'));
        return $model;
    }
    public function somethingAction()
    {
        return new ModelAndView('some');
    }
}
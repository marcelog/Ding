<?php
/**
 * This class will test the base dispatcher.
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
use Ding\Mvc\Action;
use Ding\Mvc\Http\HttpInterceptor;
use Ding\Mvc\Http\HttpFrontController;
use Ding\Mvc\DispatchInfo;
use Ding\Mvc\Http\HttpAction;
use Ding\Container\Impl\ContainerImpl;
use Ding\Mvc\ModelAndView;

/**
 * This class will test the base dispatcher.
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
class Test_HttpDispatcher extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array('filename' => 'mvc.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_dispatch_with_pre_and_post_interceptors()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyInterceptedController/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }

    /**
     * @test
     */
    public function can_interrupt_dispatch_chain_with_false()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyInterruptedInterceptedController/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertFalse($model);
        $this->assertTrue(MyInterceptor3::$boolean);
        $this->assertFalse(MyInterceptor4::$boolean);
    }

    /**
     * @test
     */
    public function can_interrupt_dispatch_chain_with_modelandview()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyMAVInterruptedInterceptedController/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'main');
    }

    /**
     * @test
     */
    public function can_dispatch()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyController/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }

    /**
     * @test
     */
    public function can_dispatch_to_main()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $handler = $container->getBean('myController');
        $action = new HttpAction('/MyController');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'main');
    }

    /**
     * @test
     */
    public function can_fill_args()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $handler = $container->getBean('myController');
        $action = new HttpAction('/MyController/someOptionalArguments', array('arg1' => 'value'));
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'main');
        $this->assertEquals(AController::$optionalValue, 'optionalValue');
        $this->assertEquals(AController::$nonOptionalValue, 'value');
    }

    /**
     * @test
     */
    public function can_dispatch_without_begin_slash()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $handler = $container->getBean('myController');
        $action = new HttpAction('MyControllerNoSlash/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }

    /**
     * @test
     * @expectedException Ding\Mvc\Exception\MvcException
     */
    public function cannot_dispatch_to_invalid_action()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $handler = $container->getBean('myController');
        $action = new HttpAction('/MyController/somethingInvalid');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
    }

    /**
     * @test
     */
    public function cannot_dispatch_to_invalid_controller()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyControllerInvalid/something');
        $dispatchInfo = $mapper->map($action);
        $this->assertFalse($dispatchInfo);
    }

    /**
     * @test
     */
    public function can_dispatch_with_annotations()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(__DIR__)),
        				'xml' => array('filename' => 'mvc.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyAnnotatedController/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }
    /**
     * @test
     * @expectedException Ding\Mvc\Exception\MvcException
     */
    public function cannot_dispatch_with_missing_argument()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyController/withRequiredArgument');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
    }

    /**
     * @test
     */
    public function can_dispatch_multiple_with_annotations()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(__DIR__)),
        				'xml' => array('filename' => 'mvc.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyAnnotatedController1/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
        $action = new HttpAction('/MyAnnotatedController2/something');
        $dispatchInfo = $mapper->map($action);
        $model = $dispatcher->dispatch($dispatchInfo);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }
}

class AController
{
    public static $optionalValue;
    public static $nonOptionalValue;

    public function mainAction()
    {
        return new ModelAndView('main');
    }

    public function withRequiredArgumentAction($someArgument)
    {

    }
    public function someOptionalArgumentsAction($arg1, $arg2 = 'optionalValue')
    {
        self::$optionalValue = $arg2;
        self::$nonOptionalValue = $arg1;
        return new ModelAndView('main');
    }
    public function somethingAction()
    {
        return new ModelAndView('blah');
    }
}

/**
 * @Controller
 * @RequestMapping(url=/MyAnnotatedController)
 */
class AnnotatedController
{
    public function somethingAction()
    {
        return new ModelAndView('blah');
    }
}

/**
 * @Controller
 * @RequestMapping(url={/MyAnnotatedController1, /MyAnnotatedController2})
 */
class MultipleAnnotatedController
{
    public function somethingAction()
    {
        return new ModelAndView('blah');
    }
}

/**
 * @Controller
 */
class UnMappedAnnotatedController
{

}

/**
 * @Controller
 * @RequestMapping
 */
class UnMappedURLAnnotatedController
{

}

class MyInterceptor3 extends HttpInterceptor
{
    public static $boolean = false;

    public function preHandle(Action $action, $handler)
    {
        self::$boolean = true;
        return false;
    }

    public function postHandle(Action $action, $handler)
    {
        return true;
    }
}

class MyInterceptor4 extends HttpInterceptor
{
    public static $boolean = false;

    public function preHandle(Action $action, $handler)
    {
        self::$boolean = true;
        return true;
    }

    public function postHandle(Action $action, $handler)
    {
        return true;
    }
}

class MyInterceptor5 extends HttpInterceptor
{
    public function preHandle(Action $action, $handler)
    {
        return new ModelAndView('main');
    }

    public function postHandle(Action $action, $handler)
    {
        return true;
    }
}

class MyInterceptor6 extends HttpInterceptor
{
    public function preHandle(Action $action, $handler)
    {
        return false;
    }

    public function postHandle(Action $action, $handler)
    {
        return true;
    }
}

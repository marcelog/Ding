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
use Ding\MVC\Http\HttpAction;
use Ding\Container\Impl\ContainerImpl;
use Ding\MVC\ModelAndView;

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
    public function can_dispatch()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyController/something');
        $model = $dispatcher->dispatch($action, $mapper);
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
        $action = new HttpAction('/MyController');
        $model = $dispatcher->dispatch($action, $mapper);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'main');
    }

    /**
     * @test
     */
    public function can_dispatch_without_begin_slash()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('MyControllerNoSlash/something');
        $model = $dispatcher->dispatch($action, $mapper);
        $this->assertTrue($model instanceof ModelAndView);
        $this->assertEquals($model->getName(), 'blah');
    }

    /**
     * @test
     * @expectedException Ding\MVC\Exception\MVCException
     */
    public function cannot_dispatch_to_invalid_action()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyController/somethingInvalid');
        $model = $dispatcher->dispatch($action, $mapper);
    }

    /**
     * @test
     * @expectedException Ding\MVC\Exception\MVCException
     */
    public function cannot_dispatch_to_invalid_controller()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $dispatcher = $container->getBean('HttpDispatcher');
        $mapper = $container->getBean('HttpUrlMapper');
        $action = new HttpAction('/MyControllerInvalid/something');
        $model = $dispatcher->dispatch($action, $mapper);
    }
}

class AController
{
    public function mainAction()
    {
        return new ModelAndView('main');
    }

    public function somethingAction()
    {
        return new ModelAndView('blah');
    }
}

<?php
/**
 * This class will test the HttpViewResolver.
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

use Ding\Mvc\ModelAndView;
use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the HttpViewResolver.
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
class Test_HttpViewResolver extends PHPUnit_Framework_TestCase
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
    public function can_resolve()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $resolver = $container->getBean('HttpViewResolver');
        $view = $resolver->resolve(new ModelAndView('name'));
        $ref = new ReflectionObject($view);
        $prop = $ref->getProperty('_path');
        $prop->setAccessible(true);
        $this->assertEquals($prop->getValue($view), './views/view.name.html');
        $this->assertTrue($view->getModelAndView() instanceof ModelAndView);
    }

    /**
     * @test
     */
    public function can_resolve_with_slash_at_the_end()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $resolver = $container->getBean('HttpViewResolver2');
        $view = $resolver->resolve(new ModelAndView('name'));
        $ref = new ReflectionObject($view);
        $prop = $ref->getProperty('_path');
        $prop->setAccessible(true);
        $this->assertEquals($prop->getValue($view), './views/view.name.html');
        $this->assertTrue($view->getModelAndView() instanceof ModelAndView);
    }
}
<?php
/**
 * This class will test the ContainerImpl class.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Container
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
use Ding\Reflection\ReflectionFactory;

/**
 * This class will test the ContainerImpl class.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Container
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Container extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(),
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'container.xml', 'directories' => array(RESOURCES_DIR)
                        ),
                        'yaml' => array(
                        	'filename' => 'container.yaml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_get_inexistant_pointcut()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('beanWithInvalidAspect');
    }

    /**
     * @test
     */
    public function can_get_url_resource()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $resource = $container->getResource('http://www.google.com');
        $contents = stream_get_contents($resource->getStream());
        $this->assertTrue(strlen($contents) > 0);
    }

    /**
     * @test
     */
    public function can_get_resource_without_scheme()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $resource = $container->getResource('somefile.txt');
        $this->assertTrue($resource instanceof Ding\Resource\Impl\FileSystemResource);
        $this->assertEquals($resource->getURL(), 'file://somefile.txt');
    }

    /**
     * @test
     */
    public function can_serialize()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        serialize($container);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function canott_instantiate_abstract_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $container->getBean('abstractBean');
    }
}

class SomeContainerTestBeanClass
{

}

class SomeContainerTestAspectClass
{

}

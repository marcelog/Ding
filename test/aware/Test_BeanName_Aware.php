<?php
/**
 * This class will test the IBeanNameAware feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
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
use Ding\Bean\IBeanNameAware;
use Ding\Container\IContainer;

/**
 * This class will test the IBeanNameAware feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_BeanName_Aware extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array(
                        	'scanDir' => array(realpath(__DIR__))
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_be_bean_name_aware()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleBeanNameAwareBean');
        $this->assertEquals($bean->name, 'aSimpleBeanNameAwareBean');
    }
}

/**
 * @Configuration
 */
class ClassBeanNameAwareConfiguration
{
    /**
     * @Bean(class=ClassBeanNameAware)
     * @Scope(value=singleton)
     */
    public function aSimpleBeanNameAwareBean()
    {
        return new ClassBeanNameAware();
    }
}

class ClassBeanNameAware implements IBeanNameAware
{
    public $name = null;

    public function setBeanName($name)
    {
        $this->name = $name;
    }

    public function __construct()
    {
    }
}

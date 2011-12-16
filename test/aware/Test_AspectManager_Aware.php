<?php
/**
 * This class will test the IAspectManagerAware feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
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

use Ding\Aspect\AspectManager;
use Ding\Aspect\IAspectManagerAware;
use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the IAspectManagerAware feature.
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
class Test_AspectManager_Aware extends PHPUnit_Framework_TestCase
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
    public function can_be_aspect_manager_aware()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleAspectManagerAwareBean');
        $this->assertTrue($bean->aManager instanceof AspectManager);
    }
}

/**
 * @Configuration
 */
class ClassAspectManagerAwareConfiguration
{
    /**
     * @Bean(class=ClassAspectManagerAware)
     * @Scope(value=singleton)
     */
    public function aSimpleAspectManagerAwareBean()
    {
        return new ClassAspectManagerAware();
    }
}

class ClassAspectManagerAware implements IAspectManagerAware
{
    public $aManager = null;

    public function setAspectManager(AspectManager $aspectManager)
    {
        $this->aManager = $aspectManager;
    }

    public function __construct()
    {
    }
}

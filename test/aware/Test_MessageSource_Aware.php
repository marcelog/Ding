<?php
/**
 * This class will test the IMessageAware feature.
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

use Ding\MessageSource\IMessageSource;
use Ding\Container\Impl\ContainerImpl;
use Ding\MessageSource\IMessageSourceAware;

/**
 * This class will test the IMessageAware feature.
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
class Test_MessageSource_Aware extends PHPUnit_Framework_TestCase
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
    public function can_be_message_source_aware()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleMessageSourceAwareBean');
        $this->assertTrue($bean->messageSource instanceof IMessageSource);
    }
}

/**
 * @Configuration
 */
class ClassMessageSourceAwareConfiguration
{
    /**
     * @Bean(class=ClassMessageSourceAware)
     * @Scope(value=singleton)
     */
    public function aSimpleMessageSourceAwareBean()
    {
        return new ClassMessageSourceAware();
    }
}

class ClassMessageSourceAware implements IMessageSourceAware
{
    public $messageSource = null;

    public function setMessageSource(IMessageSource $messageSource)
    {
        $this->messageSource = $messageSource;
    }

    public function __construct()
    {
    }
}

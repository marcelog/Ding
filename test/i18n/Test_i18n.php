<?php
/**
 * This class will test the i18n feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage i18n
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

use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the i18n feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage i18n
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_i18n extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array('filename' => 'i18n.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_resolve_message_with_arguments()
    {
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . RESOURCES_DIR);
        $container = ContainerImpl::getInstance($this->_properties);
        $this->assertEquals(
            $container->getMessage('abundle', 'message.some', array('1', '2', '3'), 'es_AR'),
            "Este es un mensaje de prueba, arg1=1 arg2=2 arg3=3"
        );
    }

    /**
     * @test
     */
    public function can_resolve_unknown_message_with_arguments()
    {
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . RESOURCES_DIR);
        $container = ContainerImpl::getInstance($this->_properties);
        $this->assertFalse(
            $container->getMessage('abundle', 'message.some2', array('1', '2', '3'), 'es_AR')
        );
    }
}
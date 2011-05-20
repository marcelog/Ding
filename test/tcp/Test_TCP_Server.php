<?php
declare(ticks=1);
/**
 * This class will test the TCP Server.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Tcp
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
use Ding\Helpers\TCP\ITCPServerHandler;
/**
 * This class will test the TCP Server.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Tcp
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_TCP_Server extends PHPUnit_Framework_TestCase
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
                        	'filename' => 'tcpserver.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_open()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server');
        $server->open();
        $server->close();
    }

    /**
     * @test
     * @expectedException Ding\Helpers\TCP\Exception\TCPException
     */
    public function cannot_bind()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server2');
        $server->open();
        $server->close();
    }

    /**
     * @test
     * expectedException Ding\Helpers\TCP\Exception\TCPException
     */
    public function cannot_bind_with_invalid_backlog()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server3');
        $server->open();
        $server->close();
    }
}
class MyServerHandler implements ITCPServerHandler
{
    public function setServer(\Ding\Helpers\TCP\TCPServerHelper $server)
    {
        $this->server = $server;
    }

    public function beforeOpen()
    {
    }

    public function beforeListen()
    {
    }

    public function close()
    {
    }

    public function handleConnection($remoteAddress, $remotePort)
    {
    }

    public function readTimeout($remoteAddress, $remotePort)
    {
    }

    public function handleData($remoteAddress, $remotePort)
    {
    }

    public function disconnect($remoteAddress, $remotePort)
    {
    }
}
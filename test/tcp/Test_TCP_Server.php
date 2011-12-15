<?php
declare(ticks=1);
/**
 * This class will test the Tcp Server.
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
use Ding\Helpers\Tcp\ITcpServerHandler;
use Ding\Helpers\Tcp\ITcpClientHandler;

/**
 * This class will test the Tcp Server.
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
class Test_Tcp_Server extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        global $mockSocketCreate;
        global $mockSocketSelect;
        $mockSocketCreate = false;
        $mockSocketSelect = false;
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
     * @expectedException Ding\Helpers\Tcp\Exception\TcpException
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
     */
    public function can_accept_connection_and_receive_data()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server');
        $server->open();
        MyServerHandler::doClient($container->getBean('Client'));
        while (strlen(MyServerHandler::$data) < 1) {
            usleep(1000);
        }
        $this->assertEquals(MyServerHandler::$data, "Hi!\n");
        $server->close();
    }

    /**
     * @test
     */
    public function can_accept_connection_and_send_data()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server');
        $server->open();
        MyServerHandler::doClient($container->getBean('Client'));
        while (strlen(MyServerHandler::$data) < 1) {
            usleep(1000);
        }
        $this->assertEquals(MyClientHandler2::$data, "Hi!\n");
        $server->close();
    }

    /**
     * @test
     */
    public function can_close_on_client_disconnect()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server5');
        $server->open();
        MyServerHandler::doClient($container->getBean('Client2'));
        while (strlen(MyServerHandler2::$data) < 1) {
            usleep(1000);
        }
        $this->assertEquals(MyServerHandler2::$data, "disconnect");
        $server->close();
    }

    /**
     * @test
     */
    public function can_timeout_on_starving_reading()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server4');
        $server->open();
        MyServerHandler::doClient($container->getBean('Client'));
        while (strlen(MyServerHandler::$data) < 1) {
            usleep(1000);
        }
        $this->assertEquals(MyServerHandler::$data, "timeout");
        $server->close();
    }
}

class MyServerHandler implements ITcpServerHandler
{
    public static $data;

    public function beforeOpen()
    {
    }

    public function beforeListen()
    {
    }

    public function close()
    {
    }

    public function handleConnection(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        $peer->write("Hi!\n");
    }

    public function readTimeout(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        self::$data = 'timeout';
    }

    public function handleData(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        $buffer = '';
        $len = 1024;
        $peer->read($buffer, $len);
        self::$data = $buffer;
    }

    public static function doClient($client)
    {
        $client->open();
        sleep(2);
    }

    public function disconnect(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
    }
}

class MyServerHandler2 implements ITcpServerHandler
{
    public static $data;

    public function beforeOpen()
    {
    }

    public function beforeListen()
    {
    }

    public function close()
    {
    }

    public function handleConnection(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
    }

    public function readTimeout(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        self::$data = 'timeout';
    }

    public function handleData(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        $buffer = '';
        $len = 1024;
        self::$data = $buffer;
    }

    public static function doClient($client)
    {
        $client->open();
        sleep(2);
    }

    public function disconnect(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        self::$data = 'disconnect';
    }
}
class MyClientHandler2 implements ITcpClientHandler
{
    public static $time;
    protected $client;
    public static $data;

    public function connectTimeout()
    {
    }

    public function readTimeout()
    {
    }
    public function beforeConnect()
    {
    }

    public function connect()
    {
        $this->client->write("Hi!\n");
    }

    public function disconnect()
    {
    }

    public function setClient(\Ding\Helpers\Tcp\TcpClientHelper $client)
    {
        $this->client = $client;
    }

    public function data()
    {
        $buffer = '';
        $len = 4096;
        $this->client->read($buffer, $len);
        self::$data = $buffer;
        $this->client->close();
    }
}
class MyClientHandler3 implements ITcpClientHandler
{
    public static $time;
    protected $client;
    public static $data;

    public function connectTimeout()
    {
    }

    public function readTimeout()
    {
    }
    public function beforeConnect()
    {
    }

    public function connect()
    {
        $this->client->close();
    }

    public function disconnect()
    {
    }

    public function setClient(\Ding\Helpers\Tcp\TcpClientHelper $client)
    {
        $this->client = $client;
    }

    public function data()
    {
        $buffer = '';
        $len = 4096;
        $this->client->read($buffer, $len);
        self::$data = $buffer;
        $this->client->close();
    }
}
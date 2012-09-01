<?php
declare(ticks=1);
$mockSocketCreate = false;
$mockSocketSelect = false;

/**
 * This class will test the Tcp Client.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Tcp
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
use Ding\Helpers\Tcp\ITcpClientHandler;
use Ding\Helpers\Tcp\ITcpServerHandler;

/**
 * This class will test the Tcp Client.
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
class Test_Tcp_Client extends PHPUnit_Framework_TestCase
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
                        	'filename' => 'tcpclient.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     * @expectedException Ding\Helpers\Tcp\Exception\TcpException
     */
    public function cannot_bind()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client');
        $client->open('1.1.1.1', 1);
    }

    /**
     * @test
     * @expectedException Ding\Helpers\Tcp\Exception\TcpException
     */
    public function cannot_connect()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client3');
        $client->open('127.0.0.1', rand(2000, 65535));
    }

    /**
     * @test
     */
    public function can_timeout_on_connect()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client');
        $start = time();
        $client->open();
        while (MyClientHandler::$time < 1) {
            usleep(1000);
        }
        $length = MyClientHandler::$time - $start;
        $this->assertTrue($length >= 10 && $length <= 15);
    }

    /**
     * @test
     */
    public function can_connect_and_receive_nonblocking()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client2');
        $client->open();
        while (strlen(MyClientHandler::$data) < 1) {
            usleep(1000);
        }
        $this->assertContains('Content-Type', MyClientHandler::$data);
    }
    /**
     * @test
     */
    public function can_connect_and_receive_blocking()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client4');
        $client->open();
        while (strlen(MyClientHandler::$data) < 1) {
            usleep(1000);
        }
        $this->assertContains('Content-Type', MyClientHandler::$data);
    }

    /**
     * @test
     */
    public function can_timeout_on_starving_reading()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $client = $container->getBean('Client5');
        $start = time();
        $client->open();
        while (MyClientHandler::$time < 1) {
            usleep(1000);
        }
        $length = MyClientHandler::$time - $start;
        $this->assertTrue($length >= 10 && $length <= 15);
    }
    /**
     * @test
     */
    public function can_close_on_server_disconnect()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $server = $container->getBean('Server');
        $server->open();
        MyServerHandler::doClient($container->getBean('Client6'));
        $server->close();
        $this->assertEquals(MyClientHandler666::$data, "disconnect");
    }
}

class MyClientHandler implements ITcpClientHandler
{
    public static $time;
    protected $client;
    public static $data;

    public function connectTimeout()
    {
        self::$time = time();
    }

    public function readTimeout()
    {
        self::$time = time();
    }
    public function beforeConnect()
    {
    }

    public function connect()
    {
        $this->client->write("GET / HTTP/1.1\nhost:www.google.com\n\n");
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
class MyClientHandler666 implements ITcpClientHandler
{
    public static $time;
    protected $client;
    public static $data;

    public function connectTimeout()
    {
        self::$time = time();
    }

    public function readTimeout()
    {
        self::$time = time();
    }
    public function beforeConnect()
    {
    }

    public function connect()
    {
    }

    public function disconnect()
    {
        self::$data = 'disconnect';
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
class MyServerHandler666 implements ITcpServerHandler
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
        $peer->disconnect();
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

<?php
/**
 * Tcp Client helper. You need to declare(ticks) in your own source code or
 * manually call process() in an infinite loop from your software.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
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
namespace Ding\Helpers\Tcp;

use Ding\Helpers\Tcp\Exception\TcpException;

/**
 * Tcp Client helper. You need to declare(ticks) in your own source code or
 * manually call process() in an infinite loop from your software.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Tcp
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class TcpClientHelper
{
    /**
     * Target port
     * @var integer
     */
    private $_port;

    /**
     * Target host or ip address.
     * @var string
     */
    private $_address;

    /**
     * Socket resource.
     * @var socket
     */
    private $_socket;

    /**
     * Handler for this connection (your class).
     * @var ITcpClientHandler
     */
    private $_handler;

    /**
     * Minimum needed bytes in the socket before calling data() on the handler.
     * @var integer
     */
    private $_readLen;

    /**
     * Read timeout in milliseconds.
     * @var integer
     */
    private $_rTo;

    /**
     * Connection timeout in milliseconds.
     * @var integer
     */
    private $_cTo;

    /**
     * Internal flag in order to know if the socket is connected.
     * @var boolean
     */
    private $_connected;

    /**
     * Time for last data received. Used to control read timeout.
     * @var float
     */
    private $_lastDataReadTime;

    /**
     * Wether to reuse or not the binding of the socket.
     * @var boolean
     */
    private $_reuse;

    /**
     * Call this to close the connection.
     *
     * @return void
     */
    public function close()
    {
        $this->_connected = false;
        $this->_handler->disconnect();
        socket_close($this->_socket);
        $this->_socket = false;
    }

    /**
     * Call this to read data from the server. Returns the number of bytes read.
     *
     * @param string  $buffer Where to store the read data.
     * @param integer $length Maximum length of data to read.
     * @param boolean $peek   If true, will not remove the data from the socket.
     *
     * @return integer
     */
    public function read(&$buffer, $length, $peek = false)
    {
        $length = socket_recv($this->_socket, $buffer, $length, $peek ? MSG_PEEK : 0);
        return $length;
    }

    /**
     * Call this to send data to the server. Returns the number of bytes
     * sent.
     *
     * @param string $what What to send.
     *
     * @return integer
     */
    public function write($what)
    {
        return socket_send($this->_socket, $what, strlen($what), 0);
    }

    /**
     * Call this to open the connection to the server. Will also set the
     * socket non blocking and control the connection timeout.
     *
     * @param string  $address Optional output ip address.
     * @param integer $port    Optional output port.
     *
     * @throws TcpException
     * @return void
     */
    public function open($address = false, $port = false)
    {
        $this->_connected = false;
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->_socket === false) {
            throw new TcpException(
            	'Error opening socket: ' . socket_strerror(socket_last_error())
            );
        }
        if ($this->_reuse) {
            socket_set_option ($this->_socket, SOL_SOCKET, SO_REUSEADDR, 1);
        }
        if ($address !== false) {
            if (!@socket_bind($this->_socket, $address, $port)) {
                throw new TcpException(
                	'Error binding socket: ' . socket_strerror(socket_last_error())
                );
            }
        }
        if ($this->_cTo > 0) {
            socket_set_nonblock($this->_socket);
            $timer = 0;
        } else {
            socket_set_block($this->_socket);
            $timer = -1;
        }
        $result = false;
        $this->_handler->beforeConnect();
        for(; $timer < $this->_cTo; $timer++)
        {
            $result = @socket_connect(
                $this->_socket, $this->_address, intval($this->_port)
            );
            if ($result === true) {
                break;
            }
            $error = socket_last_error();
            if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
                socket_close($this->_socket);
                $error = socket_strerror($error);
                $this->_socket = false;
                throw new TcpException('Could not connect: ' . $error);
            }
            // Use the select() as a sleep.
            $read = array($this->_socket);
            $write = null;
            $ex = null;
            $result = @socket_select($read, $write, $ex, 0, 650);
        }
        if (!$result) {
            $this->_handler->connectTimeout();
            socket_close($this->_socket);
            $this->_socket = false;
            return;
        }
        socket_set_nonblock($this->_socket);
        $this->_lastDataReadTime = $this->getMicrotime();
        $this->_connected = true;
        $this->_handler->connect();
        register_tick_function(array($this, 'process'));
    }

    /**
     * From php examples. Returns time including millseconds.
     *
     * @todo duplicated code, where can this go?
     * @return float
     */
    protected function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Main reading loop. Call this in your own infinite loop or declare(ticks)
     * in your software. This routine will call your client handler when there
     * is data available to read. Will always detect when the other side closed
     * the connection.
     *
     * @return void
     */
    public function process()
    {
        if ($this->_socket === false || !$this->_connected) {
            return;
        }
        $read = array($this->_socket);
        $write = null;
        $ex = null;
        $result = @socket_select($read, $write, $ex, 0, 1);
        if ($result === false) {
            throw new TcpException(
            	'Error selecting from socket: '
                . socket_strerror(socket_last_error($this->_socket))
            );
        }
        if ($result > 0) {
            if (in_array($this->_socket, $read)) {
                $buffer = '';
                $len = 1;
                $len = $this->read($buffer, $len, true);
                if ($len > 0) {
                    if ($len >= $this->_readLen) {
                        $this->_lastDataReadTime = $this->getMicrotime();
                        $this->_handler->data();
                        return;
                    }
                } else {
                    $this->close();
                    return;
                }
            }
        }
        $now = $this->getMicrotime();
        if (($now - $this->_lastDataReadTime) > $this->_rTo) {
            if ($this->_rTo > 0) {
                $this->_handler->readTimeout();
            }
            $this->_lastDataReadTime = $now;
        }
    }

    /**
     * Minimum needed bytes available in the socket before calling data() on the
     * client handler.
     *
     * @param integer $rLen Minimum data needed in socket.
     *
     * @return void
     */
    public function setReadMinLength($rLen)
    {
        $this->_readLen = intval($rLen);
    }

    /**
     * Sets the read timeout in milliseconds. 0 to disable.
     *
     * @param integer $rTo Read timeout.
     *
     * @return void
     */
    public function setReadTimeout($rTo)
    {
        $this->_rTo = (float)($rTo / 1000);
    }

    /**
     * Sets connection timeout in milliseconds. 0 to disable.
     *
     * @param integer $cTo Connection timeout.
     *
     * @return void
     */
    public function setConnectTimeout($cTo)
    {
        $this->_cTo = intval($cTo);
    }

    /**
     * Sets the tcp client handler.
     *
     * @param ITcpClientHandler $handler Client handler to use for callbacks.
     *
     * @return void
     */
    public function setHandler(ITcpClientHandler $handler)
    {
        $this->_handler = $handler;
        $handler->setClient($this);
    }

    /**
     * Sets server port.
     *
     * @param integer $port Server port.
     *
     * @return void
     */
    public function setPort($port)
    {
        $this->_port = $port;
    }

    /**
     * Sets server host or ip address.
     *
     * @param string $address Server host or ip address.
     *
     * @return void
     */
    public function setAddress($address)
    {
        $this->_address = $address;
    }

    /**
     * Sets wether to reuse or not the socket bind.
     *
     * @param boolean $reuse True to reuse the binding address.
     *
     * @return void
     */
    public function setReuse($reuse)
    {
        $this->_reuse = $reuse;
    }

    /**
     * Constructor. Not much to see here. Will register a tick function(),
     * process().
     *
     * @return void
     */
    public function __construct()
    {
        $this->_handler = false;
        $this->_socket = false;
        $this->_address = false;
        $this->_port = false;
        $this->_cTo = 0;
        $this->_rTo = 0;
        $this->_rLen = 1;
        $this->_connected = false;
        $this->_reuse = false;
    }
}
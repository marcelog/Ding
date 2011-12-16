<?php
/**
 * Tcp Server helper. You need to declare(ticks) in your own source code or
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
 * Tcp Server helper. You need to declare(ticks) in your own source code or
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
class TcpServerHelper
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
     * Maximum number of connections to handle at any given time.
     * @var integer
     */
    private $_backlog;

    /**
     * Internal flag in order to know if the socket is connected.
     * @var boolean
     */
    private $_open;

    /**
     * Holds peers sockets.
     * @var socket[]
     */
    private $_peersSockets;
    /**
     * Holds peers.
     * @var \Ding\Helpers\Tcp\TcpPeer[]
     */
    private $_peers;

    /**
     * Holds last time for received data for each peer.
     * @var float[]
     */
    private $_peersLastDataReceived;

    /**
     * Wether to reuse or not the binding of the socket.
     * @var boolean
     */
    private $_reuse;

    /**
     * Call this to close the server.
     *
     * @return void
     */
    public function close()
    {
        $this->_open = false;
        $this->_peers = array();
        $this->_peersSockets = array();
        $this->_peersLastDataReceived = array();
        $this->_handler->close();
        socket_close($this->_socket);
        $this->_socket = false;
    }


    /**
     * Call this to bind the socket and start listening for connections.
     * Will also set the socket non blocking.
     *
     * @throws TcpException
     * @return void
     */
    public function open()
    {
        $this->_open = false;
        $this->_handler->beforeOpen();
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->_socket === false) {
            throw new TcpException(
            	'Error opening socket: ' . socket_strerror(socket_last_error())
            );
        }
        if ($this->_reuse) {
            socket_set_option ($this->_socket, SOL_SOCKET, SO_REUSEADDR, 1);
        }
        if (!@socket_bind($this->_socket, $this->_address, $this->_port)) {
            $error = socket_strerror(socket_last_error($this->_socket));
            socket_close($this->_socket);
            $this->_socket = false;
            throw new TcpException('Error binding socket: ' . $error);
        }
        socket_set_nonblock($this->_socket);
        $this->_handler->beforeListen();
        if (!socket_listen($this->_socket, $this->_backlog)) {
            $error = socket_strerror(socket_last_error($this->_socket));
            socket_close($this->_socket);
            $this->_socket = false;
            throw new TcpException('Error listening socket: ' . $error);
        }
        $this->_open = true;
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
     * After disconnecting a peer, this will release all the associated
     * information.
     *
     * @return void
     */
    private function _freePeer(\Ding\Helpers\Tcp\TcpPeer $peer)
    {
        $peerName = $peer->getName();
        unset($this->_peers[$peerName]);
        unset($this->_peersSockets[$peerName]);
        unset($this->_peersLastDataReceived[$peerName]);
    }
    /**
     * This will process activity in all peers. Called from process().
     *
     * @return void
     */
    public function processPeers()
    {
        // Control peers.
        if (count($this->_peers) < 1) {
            return;
        }
        $now = $this->getMicrotime();
        foreach ($this->_peers as $peerName => $peer) {
            if (!$peer->hasActivity()) {
                continue;
            }
            $buffer = '';
            $len = 1;
            $len = $peer->read($buffer, $len, true);
            if ($len > 0) {
                if ($len >= $this->_readLen) {
                    $this->_handler->handleData($peer);
                    $this->_peersLastDataReceived[$peerName] = $now;
                }
            } else {
                $peer->disconnect();
                $this->_freePeer($peer);
                $this->_handler->disconnect($peer);
            }
        }
        foreach ($this->_peers as $peerName => $peer) {
            $peerTime = $this->_peersLastDataReceived[$peerName];
            if (($now - $peerTime) > $this->_rTo) {
                if ($this->_rTo > 0) {
                    $peer->disconnect();
                    $this->_freePeer($peer);
                    $this->_handler->readTimeout($peer);
                }
                $this->_peersLastDataReceived[$peerName] = $now;
            }
        }
    }
    /**
     * Main reading loop. Call this in your own infinite loop or declare(ticks)
     * in your software. This routine will call your server handler when there
     * is data available to read, new connections, or timeouts. Will always
     * detect when the other side closed the connection.
     *
     * @return void
     */
    public function process()
    {
        if ($this->_socket === false || !$this->_open) {
            return;
        }
        // Control server.
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
                $newSocket = socket_accept($this->_socket);
                if ($newSocket !== false) {
                    $address = '';
                    $port = 0;
                    socket_getpeername($newSocket, $address, $port);
                    $peerObject = new \Ding\Helpers\Tcp\TcpPeer($address, $port, $newSocket);
                    $peername = $peerObject->getName();
                    $this->_peers[$peername] = $peerObject;
                    $this->_peersSockets[$peername] = $newSocket;
                    $this->_peersLastDataReceived[$peername] = $this->getMicrotime();
                    $this->_handler->handleConnection($peerObject);
                }
            }
        }
        $this->processPeers();
    }

    /**
     * Minimum needed bytes available in the socket before calling data() on the
     * server handler.
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
     * Sets maximum number of connections.
     *
     * @param integer $backlog Max number of connections to handle.
     *
     * @return void
     */
    public function setBacklog($backlog)
    {
        $this->_backlog = intval($backlog);
    }

    /**
     * Sets the tcp server handler.
     *
     * @param ITcpServerHandler $handler Server handler to use for callbacks.
     *
     * @return void
     */
    public function setHandler(ITcpServerHandler $handler)
    {
        $this->_handler = $handler;
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
        $this->_backlog = 0;
        $this->_rTo = 0;
        $this->_rLen = 1;
        $this->_connected = false;
        $this->_peers = array();
        $this->_peersSockets = array();
        $this->_peersLastDataReceived = array();
        $this->_reuse = false;
        register_tick_function(array($this, 'process'));
    }
}
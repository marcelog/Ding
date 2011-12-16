<?php
/**
 * Tcp Peer: encapsulated data and operations for each peer conected to the
 * Tcp Server helper.
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
 * Tcp Peer: encapsulated data and operations for each peer conected to the
 * Tcp Server helper.
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
class TcpPeer
{
    /**
     * @var string
     */
    protected $address;
    /**
     * @var integer
     */
    protected $port;

    /**
     * @var resource
     */
    protected $socket;
    /**
     * Writes to this peer.
     *
     * @param string $what What to write.
     *
     * @return integer
     */
    public function write($what)
    {
        $ret = null;
        if ($this->isConnected()) {
            $ret = @socket_send($this->socket, $what, strlen($what), 0);
        }
        return $ret;
    }
    /**
     * Writes to this peer.
     *
     * @param string  &$buffer Where to put read data.
     * @param integer $length  Maximum data to read.
     * @param boolean $peek    True to just take a peek to the data.
     *
     * @return integer
     */
    public function read(&$buffer, $length, $peek = false)
    {
        $ret = null;
        if ($this->isConnected()) {
            $length = @socket_recv($this->socket, $buffer, $length, $peek ? MSG_PEEK : 0);
            $ret = $length;
        }
        return $ret;
    }
    /**
     * Call this to disconnect the peer.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            @socket_close($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Returns true if this peer is still connected.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->socket !== null;
    }
    /**
     * Call this one to see if this peer has any activity in the socket.
     *
     * @return boolean
     */
    public function hasActivity()
    {
        if (!$this->isConnected()) {
            return false;
        }
        $read = array($this->socket);
        $write = null;
        $ex = null;
        $result = socket_select($read, $write, $ex, 0, 0);
        if ($result === false) {
            throw new TcpException(
            	'Error selecting from socket: '
                . socket_strerror(socket_last_error($this->socket))
            );
        }
        return !empty($read);
    }

    /**
     * Returns the ip address for this peer.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the port number for this peer.
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns ip:port
     *
     * @return string
     */
    public function getName()
    {
        return $this->address . ':' . $this->port;
    }

    /**
     * Returns open socket for this peer.
     *
     * @return resoucce
     */
    public function getSocket()
    {
        return $this->socket;
    }
    /**
     * Constructor.
     *
     * @param string   $address IP for this peer.
     * @param integer  $port    Port number for this peer.
     * @param resource $socket Socket for this peer.
     */
    public function __construct($address, $port, $socket)
    {
        $this->address = $address;
        $this->port = $port;
        $this->socket = $socket;
    }
}
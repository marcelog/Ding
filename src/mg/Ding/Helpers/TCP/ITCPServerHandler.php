<?php
/**
 * TCP Server handler interface. Implement this in your own classes.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
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
namespace Ding\Helpers\TCP;

/**
 * TCP Server handler interface. Implement this in your own classes.
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
interface ITCPServerHandler
{
    /**
     * Called by the helper to inject itself into this handler.
	 *
     * @param \Ding\Helpers\TCP\TCPServerHelper $server TCP Server helper.
     *
     * @return void
     */
    public function setServer(\Ding\Helpers\TCP\TCPServerHelper $server);

    /**
     * Called before opening and binding the server socket.
     *
     * @return void
     */
    public function beforeOpen();

    /**
     * Called before listening the socket.
     *
     * @return void
     */
    public function beforeListen();

    /**
     * Called after closing the server socket.
     *
     * @return void
     */
    public function close();

    /**
     * Called when a new client connects.
     *
	 * @param string  $remoteAddress Client ip address.
	 * @param integer $remotePort    Client port.
	 *
     * @return void
     */
    public function handleConnection($remoteAddress, $remotePort);

    /**
     * Called when a client has sent data and is ready to be read.
     *
	 * @param string  $remoteAddress Client ip address.
	 * @param integer $remotePort    Client port.
	 *
     * @return void
     */
    public function handleData($remoteAddress, $remotePort);

    /**
     * Called when a client disconnects.
     *
	 * @param string  $remoteAddress Client ip address.
	 * @param integer $remotePort    Client port.
	 *
     * @return void
     */
    public function disconnect($remoteAddress, $remotePort);

    /**
	 * Called when a client timeouts on reading data.
	 *
	 * @param string  $remoteAddress Client ip address.
	 * @param integer $remotePort    Client port.
	 *
	 * @return void
     */
    public function readTimeout($remoteAddress, $remotePort);
}
<?php
/**
 * Tcp Server handler interface. Implement this in your own classes.
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

/**
 * Tcp Server handler interface. Implement this in your own classes.
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
interface ITcpServerHandler
{
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
	 * @param \Ding\Helpers\Tcp\TcpPeer $peer Peer triggering the event.
	 *
     * @return void
     */
    public function handleConnection(\Ding\Helpers\Tcp\TcpPeer $peer);

    /**
     * Called when a client has sent data and is ready to be read.
     *
	 * @param \Ding\Helpers\Tcp\TcpPeer $peer Peer triggering the event.
	 *
     * @return void
     */
    public function handleData(\Ding\Helpers\Tcp\TcpPeer $peer);

    /**
     * Called when a client disconnects.
     *
	 * @param \Ding\Helpers\Tcp\TcpPeer $peer Peer triggering the event.
	 *
     * @return void
     */
    public function disconnect(\Ding\Helpers\Tcp\TcpPeer $peer);

    /**
	 * Called when a client timeouts on reading data.
	 *
	 * @param \Ding\Helpers\Tcp\TcpPeer $peer Peer triggering the event.
	 *
	 * @return void
     */
    public function readTimeout(\Ding\Helpers\Tcp\TcpPeer $peer);
}
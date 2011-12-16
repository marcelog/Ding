<?php
/**
 * Tcp Client handler interface. Implement this in your own classes.
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
 * Tcp Client handler interface. Implement this in your own classes.
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
interface ITcpClientHandler
{
    /**
     * Called by the helper to inject itself into this handler.
	 *
     * @param \Ding\Helpers\Tcp\TcpClientHelper $client Tcp Client helper.
     *
     * @return void
     */
    public function setClient(\Ding\Helpers\Tcp\TcpClientHelper $client);

    /**
     * Called just before trying to connect to the server.
     *
     * @return void
     */
    public function beforeConnect();

    /**
     * Called when the connection was successfull.
     *
     * @return void
     */
    public function connect();

    /**
     * Called when a connection timeout has happened.
     *
     * @return void
     */
    public function connectTimeout();

    /**
     * Called when the connection has been closed by either side.
     *
     * @return void
     */
    public function disconnect();

    /**
     * Called when data is available to read.
     *
     * @return void
     */
    public function data();

    /**
     * Called when a read timeout has happened.
     *
	 * @return void
     */
    public function readTimeout();
}
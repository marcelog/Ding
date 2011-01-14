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
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
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
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface ITCPServerHandler
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
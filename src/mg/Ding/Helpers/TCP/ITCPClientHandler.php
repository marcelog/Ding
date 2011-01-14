<?php
/**
 * TCP Client handler interface. Implement this in your own classes.
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
 * TCP Client handler interface. Implement this in your own classes.
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
interface ITCPClientHandler
{
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
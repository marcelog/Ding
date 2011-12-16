<?php
/**
 * PAMI Helper. Will call your own handler and manage ami connection.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pami
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
namespace Ding\Helpers\Pami;

use PAMI\Client\Impl\ClientImpl;
use PAMI\Listener\IEventListener;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Action\ActionMessage;

/**
 * PAMI Helper. Will call your own handler and manage ami connection.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pami
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class PamiHelper implements IEventListener
{
    /**
     * AMI Host.
     * @var string
     */
    private $_host;

    /**
     * AMI Port.
     * @var integer
     */
    private $_port;

    /**
     * AMI Username.
     * @var string
     */
    private $_user;

    /**
     * AMI Password.
     * @var string
     */
    private $_pass;

    /**
     * AMI Connect timeout
     * @var integer
     */
    private $_connect_timeout;

    /**
     * AMI Read timeout
     * @var integer
     */
    private $_read_timeout;

    /**
     * Handler.
     * @var IPamiEventHandler
     */
    private $_handler;

    /**
     * Internally used to manage initialization.
     * @var boolean
     */
    private $_init;

    /**
     * PAMI object.
     * @var ClientImpl
     */
    private $_ami;

    /**
     * Sets ami host
     *
     * @param string $host Host name or ip address.
     *
     * @return void
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * Sets ami port.
     *
     * @param integer $port AMI Port.
     *
     * @return void
     */
    public function setPort($port)
    {
        $this->_port = intval($port);
    }

    /**
     * Sets ami username.
     *
     * @param string $user AMI Username.
     *
     * @return void
     */
    public function setUsername($user)
    {
        $this->_user = $user;
    }

    /**
     * Sets ami password.
     *
     * @param string $pass AMI Password.
     *
     * @return void
     */
    public function setPassword($pass)
    {
        $this->_pass = $pass;
    }

    /**
     * Sets ami connect timeout.
     *
     * @param integer $time AMI Connect timeout in milliseconds.
     *
     * @return void
     */
    public function setConnectTimeout($time)
    {
        $this->_connect_timeout = $time;
    }

    /**
     * Sets ami read timeout.
     *
     * @param integer $time AMI Read timeout in milliseconds.
     *
     * @return void
     */
    public function setReadTimeout($time)
    {
        $this->_read_timeout = $time;
    }

    /**
     * Sets handler.
     *
     * @param IPamiEventHandler $handler Handler to call.
     *
     * @return void
     */
    public function setHandler(IPamiEventHandler $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * Our own handler, will call yours.
     *
     * @param EventMessage $event Incoming event.
     *
     * @return void
     */
    public function handle(EventMessage $event)
    {
        $this->_handler->handlePamiEvent($event);
    }

    /**
     * Loads pami and initializes everything.
     *
     * @return void
     */
    private function _load()
    {
        $options = array(
            'host' => $this->_host,
            'port' => $this->_port,
            'username' => $this->_user,
            'secret' => $this->_pass,
            'connect_timeout' => $this->_connect_timeout,
            'read_timeout' => $this->_read_timeout
        );
        $this->_ami = new ClientImpl($options);
        $this->_init = true;
    }

    /**
     * Opens the connection to ami. Also calls _load if it has to.
     *
     * @return void
     */
    public function open()
    {
        if (!$this->_init) {
            $this->_load();
        }
	    $this->_ami->registerEventListener($this);
	    $this->_ami->open();
    }

    /**
     * Process all incoming messages. Call this one from your own loop.
     *
     * @return void
     */
    public function process()
    {
        $this->_ami->process();
    }

    /**
     * Sends a message to ami.
     *
     * @param ActionMessage $message AMI Command.
     *
     * @return Response
     */
    public function send(ActionMessage $message)
    {
        return $this->_ami->send($message);
    }

    /**
     * Closes the connection to ami.
     *
     * return void
     */
    public function close()
    {
        $this->_ami->close();
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_init = false;
        $this->_connect_timeout = 0;
        $this->_read_timeout = 0;
    }
}

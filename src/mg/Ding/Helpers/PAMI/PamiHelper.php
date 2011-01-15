<?php
/**
 * PAMI Helper. Will call your own handler and manage ami connection.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage PAMI
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\PAMI;

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
 * @subpackage PAMI
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
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
	    $this->_ami = new ClientImpl(
	        $this->_host, $this->_port, $this->_user, $this->_pass, 0, 0
	    );
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
    }
}
<?php
/**
 * This is a bean that will call your own signal handler (only if it implements
 * ISignalHandler). YOU MUST have declare(ticks=1); AS THE FIRST LINE IN YOUR
 * SCRIPT. IT SEEMS PHP SUCKS, AND THERE IS NO WAY TO CONTROL THAT FROM THIS
 * CLASS. NOT EVEN WITH pcntl_signal_dispatch() and/or register_tick_function()
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage SignalHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\SignalHandler;

/**
 * This is a bean that will call your own signal handler (only if it implements
 * ISignalHandler). YOU MUST have declare(ticks=1); AS THE FIRST LINE IN YOUR
 * SCRIPT. IT SEEMS PHP SUCKS, AND THERE IS NO WAY TO CONTROL THAT FROM THIS
 * CLASS. NOT EVEN WITH pcntl_signal_dispatch() and/or register_tick_function()
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage SignalHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class SignalHandlerHelper
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Signal handler to call.
     * @var ISignalHandler
     */
    private $_handler;

    /**
     * Set a handler to call upon signals.
     *
     * @param ISignalHandler $handler Handler to call.
     *
     * @return void
     */
    public function setSignalHandler(ISignalHandler $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * Returns true.
     *
     * @param integer $signal Signal caught.
     *
     * @return boolean
     */
     public function handle($signal)
     {
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('Caught signal: ' . $signal);
        }
        $this->_handler->handleSignal($signal);
        return true;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = \Logger::getLogger('Ding.SignalHandlerHelper');
    }
}
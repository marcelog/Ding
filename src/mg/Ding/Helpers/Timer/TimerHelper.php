<?php
/**
 * Timer helper. Will call your handler every so milliseconds.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Timer
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\Timer;

/**
 * Timer helper. Will call your handler every so milliseconds.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Timer
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class TimerHelper
{
    /**
     * Timer handler.
     * @var ITimerHandler
     */
    private $_handler;

    /**
     * Timer interval, in milliseconds, doh.
     * @var integer
     */
    private $_milliseconds;

    /**
     * Time for last expiration (or start) of this timer.
     * @var float
     */
    private $_start;

    /**
     * Used internally for tick to discrimine when the timer should be running.
     * @var boolean
     */
    private $_running;

    /**
     * Sets the handler for this timer.
     *
     * @param ITimerHandler $handler Handler to call when timer expires.
     *
     * @return void
     */
    public function setHandler(ITimerHandler $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * Configure the timer, set the amount of milliseconds.
     *
     * @param integer $milliseconds Timer interval.
     *
     * @return void
     */
    public function setMilliseconds($milliseconds)
    {
        $this->_milliseconds = $milliseconds;
    }

    /**
     * Evaluates if the timer has expired and if so, call the handler.
     *
	 * @return void
     */
    public function tick()
    {
        if (!$this->_running) {
            return;
        }
        $end = $this->getMicrotime();
        $total = ($end - $this->_start) * 1000;
        if ($total >= $this->_milliseconds) {
            $this->_handler->handleTimer();
            $this->_start = $this->getMicrotime();
        }
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
     * Starts the timer.
     *
     * @return void
     */
    public function start()
    {
        $this->_start = $this->getMicrotime();
        $this->_running = true;
    }

    /**
     * Stops the timer.
     *
     * @return void
     */
    public function stop()
    {
        $this->_running = true;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        register_tick_function(array($this, 'tick'));
    }
}
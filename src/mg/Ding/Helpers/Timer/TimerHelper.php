<?php
declare(ticks=1);
/**
 * Timer helper. Will call your handler every so milliseconds.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Timer
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
        register_tick_function(array($this, 'tick'));
    }

    /**
     * Stops the timer.
     *
     * @return void
     */
    public function stop()
    {
        $this->_running = false;
        unregister_tick_function(array($this, 'tick'));
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_running = false;
    }
}

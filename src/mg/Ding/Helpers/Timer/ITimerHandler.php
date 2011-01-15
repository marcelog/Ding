<?php
/**
 * Timer handler interface. Implement this one in your own handler.
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
 * Timer handler interface. Implement this one in your own handler.
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
interface ITimerHandler
{
    /**
     * Called when the timer expires.
     *
     * @return void
     */
    public function handleTimer();
}
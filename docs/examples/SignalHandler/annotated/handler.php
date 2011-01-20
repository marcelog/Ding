<?php
/**
 * Example using annotated SignalHandler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage SignalHandler.Annotated
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
use Ding\Helpers\SignalHandler\ISignalHandler;

/**
 * @SignalHandler
 */
class MySignalHandler implements ISignalHandler
{
    public function handleSignal($signal)
    {
        echo "This is your custom signal handler: " . $signal . "\n";
    }

    public function __construct()
    {
    }
}

<?php
/**
 * Example using annotated ShutdownHandler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage ShutdownHandler.Annotated
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
use Ding\Helpers\ShutdownHandler\IShutdownHandler;

/**
 * @ShutdownHandler
 */
class MyShutdownHandler implements IShutdownHandler
{
    public function handleShutdown()
    {
        echo "This is your custom shutdown handler\n";
    }

    public function __construct()
    {
    }
}

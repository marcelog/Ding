<?php
/**
 * Example using annotated ErrorHandler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage ErrorHandler.Annotated
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\ErrorHandler\ErrorInfo;

/**
 * @ErrorHandler
 */
class MyErrorHandler implements IErrorHandler
{
    public function handleError(ErrorInfo $error)
    {
        echo "This is your custom error handler: $error\n";
    }

    public function __construct()
    {
    }
}


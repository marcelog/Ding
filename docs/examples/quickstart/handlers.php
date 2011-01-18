<?php
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;

/**
 * @ErrorHandler
 * @SignalHandler
 * @ShutdownHandler
 * @InitMethod(method=anInitMethod)
 * @DestroyMethod(method=aDestroyMethod)
 */
class MyErrorHandler implements IErrorHandler, ISignalHandler, IShutdownHandler
{
    public function anInitMethod()
    {
        echo "Hello, this is the init method of your errorhandler\n";
    }

    public function aDestroyMethod()
    {
        echo "Hello, this is the *destroy* method of your errorhandler\n";
    }

    public function handleError(ErrorInfo $error)
    {
        echo "This is your custom error handler: " . print_r($error, true);
    }

    public function handleShutdown()
    {
        echo "This is your custom shutdown handler.\n";
    }

    public function handleSignal($signal)
    {
        echo "This is your custom signal handler: " . $signal . "\n";
    }
}

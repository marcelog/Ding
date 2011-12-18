<?php
namespace BookStore\ErrorHandler;

use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Logger\ILoggerAware;

class ErrorHandler implements ILoggerAware
{
    protected $logger;

    public function setLogger(\Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onDingError(ErrorInfo $error)
    {
        $this->logger->error(
        	"This is your custom error handler: " . print_r($error, true)
        );
    }
}
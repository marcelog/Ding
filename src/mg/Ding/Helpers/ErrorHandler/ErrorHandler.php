<?php

namespace Ding\Helpers\ErrorHandler;

class ErrorHandler
{
    public function handle($errno, $errstr, $errfile, $errline)
    {
        $info = new ErrorInfo($errno, $errstr, $errfile, $errline);
        return true;
    }

    public function __construct()
    {
    }
}
<?php
namespace Ding\Helpers\ErrorHandler;

class ErrorInfo
{
    private $_type;
    private $_message;
    private $_file;
    private $_line;

    public function getType()
    {
        return $this->_type;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function getLine()
    {
        return $this->_line;
    }

    public function __construct($type, $message, $file, $line)
    {
        $this->_type = $type;
        $this->_message = $message;
        $this->_file = $file;
        $this->_line = $line;
    }
}
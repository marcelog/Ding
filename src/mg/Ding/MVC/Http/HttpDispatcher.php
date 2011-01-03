<?php
namespace Ding\MVC\Http;

use Ding\MVC\Dispatcher;
use Ding\MVC\IAction;

class HttpDispatcher extends Dispatcher
{
    private $_get;
    private $_post;
    private $_server;
    private $_cookies;
    private $_session;

    public function init()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_server = $_SERVER;
        $this->_cookies = $_COOKIE;
    }

    public function __construct()
    {
    }
}
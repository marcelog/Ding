<?php
namespace Ding\MVC\Http;

use Ding\MVC\IAction;

class HttpAction implements IAction
{
    private $_method;
    private $_url;
    
    public function setMethod($method)
    {
        $this->_method = $method;
    }
    
    public function getMethod($method)
    {
        return $this->_method;
    }
    
    public function setUrl($url)
    {
        $this->_url = $url;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }

    public function __construct()
    {
        $this->_method = 'GET';
        $this->_url = '/';
    }
}
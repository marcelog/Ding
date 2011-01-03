<?php
namespace Ding\MVC\Http;

use Ding\MVC\Action;

class HttpAction extends Action
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
    
    public function __construct($id, array $arguments = array())
    {
        parent::__construct($id, $arguments);
        $this->_method = 'GET';
        $this->_url = '/';
    }
}
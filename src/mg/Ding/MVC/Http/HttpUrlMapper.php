<?php
namespace Ding\MVC\Http;

use Ding\MVC\Exception\MVCException;
use Ding\MVC\IMapper;
use Ding\MVC\Action;

class HttpUrlMapper implements IMapper
{
    /**
     * @var IController[]
     */
    private $_map;

    /**
     * Assigned base url.
     * @var string
     */
    private $_baseUrl;
    
    /**
     * [0] = IAction
     * [1] = IController
     * (non-PHPdoc)
     * @see Ding\MVC.IMapper::setMap()
     */
    public function setMap(array $map)
    {
        $this->_map[] = $map;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
    }
    
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }
    
    /**
     * @param IAction $action
     * 
     * @return IController
     */
    public function map(Action $action)
    {
        $url = $action->getId();
        $urlStart = strpos($url, $this->_baseUrl);
        if ($urlStart === false || $urlStart > 0) {
            throw new MVCException('Not a base url.');
        }
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        $url = explode('?', substr($url, $urlStart + strlen($this->_baseUrl)));
        $url = $url[0];
        $len = strlen($url) - 1;
        if ($url[$len] != '/') {
            $url .= '/';
        }
        foreach ($this->_map as $map) {
            $controllerUrl = $map[0];
            $controller = $map[1];
            if ($controllerUrl[0] != '/') {
                $controllerUrl = '/' . $controllerUrl;
            }
            $len = strlen($controllerUrl);
            if ($controllerUrl[$len - 1] != '/') {
                $controllerUrl = $controllerUrl . '/';
            }
            $controllerUrlStart = strpos($url, $controllerUrl);
            if ($controllerUrlStart === false || $controllerUrlStart > 0) {
                continue;
            }
            $start = $controllerUrlStart + strlen($controllerUrl);
            $action = substr($url, $start);
            if ($action === false) {
                $action = 'Main';
            }
            $action = explode('/', $action);
            $action = $action[0]; 
            return array($controller, $action . 'Action');
        }
        return false;
    }
    
    public function __construct()
    {
        $this->_map = array();
        $this->_baseUrl = '/';
    }
}
<?php
namespace Ding\MVC\Http;

use Ding\MVC\IMapper;

class HttpUrlMapper implements IMapper
{
    /**
     * @var IController[]
     */
    private $_map;

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

    /**
     * @param IAction $action
     * 
     * @return IController
     */
    public function map(IAction $action)
    {
        foreach ($this->_map as $url => $controller) {
            if ($url === $action->getId()) {
                return array($controller, $url . 'Action');
            }
        }
    }
    
    public function __construct()
    {
        $this->_map = array();
    }
}
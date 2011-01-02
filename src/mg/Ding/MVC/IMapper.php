<?php
namespace Ding\MVC;

interface IMapper
{
    /**
     * @param IAction $action
     * 
     * @return IController
     */
    public function map(IAction $action);
    
    public function setMap(array $map);
}
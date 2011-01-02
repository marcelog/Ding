<?php
namespace Ding\MVC;

abstract class IView
{
    private $_name;

    public abstract function render(ModelAndView $modelAndView);

    public function getName()
    {
        return $this->_name;
    }

    protected function __construct($name)
    {
        $this->_name = $name;
    }
}
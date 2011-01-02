<?php
namespace Ding\MVC\Http;

use Ding\MVC\IView;
use Ding\MVC\ModelAndView;

class HttpView implements IView
{
    private $_path;
    
    public function render(ModelAndView $modelAndView)
    {
        readfile($this->_path);
        return true;
    }
    
    public function __construct($name, $path)
    {
        parent::__construct($name);
        $this->_path = $path;
    }
}
<?php
namespace Ding\MVC\Http;

use Ding\MVC\View;
use Ding\MVC\ModelAndView;

class HttpView extends View
{
    private $_path;
    
    public function render()
    {
        readfile($this->_path);
        return true;
    }
    
    public function __construct(ModelAndView $modelAndView, $path)
    {
        parent::__construct($modelAndView);
        $this->_path = $path;
    }
}
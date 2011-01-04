<?php
namespace Ding\MVC\Http;

use Ding\MVC\IViewResolver;
use Ding\MVC\ModelAndView;

class HttpViewResolver implements IViewResolver
{
    private $_path;
    private $_prefix;
    private $_name;
    private $_suffix;
    
    public function resolve(ModelAndView $modelAndView)
    {
        $name = $modelAndView->getName();
        $path
            = $this->_path .
            DIRECTORY_SEPARATOR .
            $this->_prefix . $name . $this->_suffix
        ;
        return new HttpView($modelAndView, $path);
    }

    public function setViewPath($path)
    {
        $this->_path = $path;
    }
    
    public function setViewPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }
    
    public function setViewName($name)
    {
        $this->_name = $name;
    }
    
    public function setViewSuffix($suffix)
    {
        $this->_suffix = $suffix;
    }
    
}
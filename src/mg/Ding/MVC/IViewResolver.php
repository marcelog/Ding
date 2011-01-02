<?php
namespace Ding\MVC;

interface IViewResolver
{
    /**
     * @return IView
     * @param unknown_type $modelAndView
     */
    public function resolve(ModelAndView $modelAndView);
}
<?php
namespace BookStore\Controllers;

use Ding\Mvc\ForwardModelAndView;
use Ding\Mvc\ModelAndView;

/**
 * @Controller
 * @RequestMapping(url=/)
 */
class Main
{
    public function mainAction()
    {
        $modelAndView = new ModelAndView('layout');
        $modelAndView->add(array('viewName' => 'home'));
        return $modelAndView;
    }
    public function __construct()
    {
    }
}
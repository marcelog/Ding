<?php
namespace BookStore\Controllers;

use Ding\Mvc\ModelAndView;

/**
 * @Component(name=exceptionController)
 * @Controller
 */
class Exception
{
    public function _ExceptionException($exception)
    {
        $modelAndView = new ModelAndView('layout');
        $modelAndView->add(array('viewName' => 'exception'));
        $modelAndView->add(array('exception' => $exception));
        return $modelAndView;
    }
}
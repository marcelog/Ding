<?php
namespace BookStore\Controllers;

use Ding\Mvc\RedirectModelAndView;

use Ding\HttpSession\HttpSession;
use Ding\Mvc\ForwardModelAndView;
use Ding\Mvc\ModelAndView;

/**
 * @Component(name=bookController)
 * @Controller
 * @RequestMapping(url=/Settings)
 */
class Settings
{
    public function changeLanguageAction($language)
    {
        $session = HttpSession::getSession();
        $session->setAttribute('LANGUAGE', $language);
        $modelAndView = new RedirectModelAndView('/');
        return $modelAndView;
    }
    public function __construct()
    {
    }
}
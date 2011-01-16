<?php
use Ding\MVC\ModelAndView;

/**
 * @Controller
 * @RequestMapping(url=/MyAnnotatedController)
 */
class AnnotatedController
{
    /**
     * @pepe
     */
    public function anAction()
    {
        $modelAndView = new ModelAndView('annotated');
        return $modelAndView;
    }
}
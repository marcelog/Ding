<?php
/**
 * Interface for any object capable of resolving a given ModelAndView to
 * an actual View.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\MVC;

/**
 * Interface for any object capable of resolving a given ModelAndView to
 * an actual View.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface IViewResolver
{
    /**
     * Maps a given ModelAndView to a corresponding view so it can be rendered.
     * 
     * @param ModelAndView $modelAndView
     * 
     * @throws MVCException
     * @return IView
     */
    public function resolve(ModelAndView $modelAndView);
}
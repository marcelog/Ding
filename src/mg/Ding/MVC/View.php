<?php
/**
 * A generic view.
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
 * A generic view.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
abstract class View
{
    /**
     * View name.
     * @var ModelAndView
     */
    private $_modelAndView;

    /**
     * Implement this one to render your view.
     *
     * @return void
     */
    public abstract function render();

    /**
     * Returns this view name.
     *
     * @return string
     */
    public function getModelAndView()
    {
        return $this->_modelAndView;
    }

    /**
     * Constructor.
     *
     * @param ModelAndView $modelAndView Model to render.
     * 
     * @return void
     */
    protected function __construct(ModelAndView $modelAndView)
    {
        $this->_modelAndView = $modelAndView;
    }
}
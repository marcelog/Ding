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
     * @var string
     */
    private $_name;

    /**
     * Implement this one to render your view.
     *
     * @param ModelAndView $modelAndView What to render.
     * 
     * @return void
     */
    public abstract function render(ModelAndView $modelAndView);

    /**
     * Returns this view name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Constructor.
     *
     * @param string $name View name.
     * 
     * @return void
     */
    protected function __construct($name)
    {
        $this->_name = $name;
    }
}
<?php
/**
 * An http view.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\MVC\Http;

use Ding\MVC\View;
use Ding\MVC\ModelAndView;

/**
 * An http view.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class HttpView extends View
{
    /**
     * Full absolute path for this view.
     * @var string
     */
    private $_path;

    /**
     * Renders this view.
     *
     * @see Ding\MVC.View::render()
     *
     * @return void
     */
    public function render()
    {
        /**
         * @todo is there a better way to do this?
         */
        global $modelAndView;
        $modelAndView = $this->getModelAndView();
        /**
         * @todo render headers: does this belong here?
         */
        $objects = $modelAndView->getModel();
        if (isset($objects['headers'])) {
            foreach ($objects['headers'] as $header) {
                header($header);
            }
        }
        // Now render everything else.
        if (file_exists($this->_path)) {
            include_once $this->_path;
        }
    }

    /**
     * Constructor.
     *
     * @param ModelAndView $modelAndView Use this model representation for this view.
     * @param string       $path         Full absolute path to the file containing this view.
     *
     *  @return void
     */
    public function __construct(ModelAndView $modelAndView, $path)
    {
        parent::__construct($modelAndView);
        $this->_path = $path;
    }
}
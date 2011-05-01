<?php
/**
 * Twig view.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class TwigView extends View
{
    /**
     * Full absolute path for this view.
     * @var string
     */
    private $_path;

    /**
     * TWIG options.
     * @var string[]
     */
    private $_twigOptions = array();

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
        require_once 'Twig' . DIRECTORY_SEPARATOR . 'Autoloader.php';
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(dirname($this->_path));
        $twig = new \Twig_Environment($loader, array($this->_twigOptions));
        $template = $twig->loadTemplate(basename($this->_path));
        echo $template->render(array('vars' => $modelAndView->getModel()));
    }

    /**
     * Constructor.
     *
     * @param ModelAndView $modelAndView Use this model representation for this view.
     * @param string       $path         Full absolute path to the file containing this view.
     * @param string[]     $options      TWIG options.
     *
     *  @return void
     */
    public function __construct(ModelAndView $modelAndView, $path, array $options)
    {
        parent::__construct($modelAndView);
        $this->_path = $path;
        $this->_twigOptions = $options;
    }
}
<?php
/**
 * Twig view render.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
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
namespace Ding\Mvc\Http;

use Ding\Mvc\IViewRender;
use Ding\Mvc\View;

/**
 * Twig view render.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class TwigViewRender implements IViewRender
{
    /**
     * TWIG options.
     * @var string[]
     */
    private $_twigOptions = array();

    /**
     * Sets TWIG options.
     *
     * @param string[] $options TWIG options.
     *
     * @return void
     */
    public function setTwigOptions(array $options)
    {
        $this->_twigOptions = $options;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Mvc.IViewRender::render()
     */
    public function render(View $view)
    {
        /**
         * @todo is there a better way to do this?
         */
        global $modelAndView;
        $modelAndView = $view->getModelAndView();
        require_once 'Twig' . DIRECTORY_SEPARATOR . 'Autoloader.php';
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(dirname($view->getPath()));
        $twig = new \Twig_Environment($loader, array($this->_twigOptions));
        $template = $twig->loadTemplate(basename($view->getPath()));
        echo $template->render(array('vars' => $modelAndView->getModel()));
    }
}
<?php
/**
 * Smarty view render.
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
 * Smarty view render.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class SmartyViewRender implements IViewRender
{
    /**
     * Smarty options.
     * @var string[]
     */
    private $_smartyOptions = array();

    /**
     * Sets Smarty options.
     *
     * @param string[] $options Smarty options.
     *
     * @return void
     */
    public function setSmartyOptions(array $options)
    {
        $this->_smartyOptions = $options;
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
        require_once('Smarty.class.php');
        $smarty = new \Smarty();
        $smarty->template_dir = dirname($view->getPath());
        $smarty->compile_dir = $this->_smartyOptions['compile_dir'];
        $smarty->config_dir = $this->_smartyOptions['config_dir'];
        $smarty->cache_dir = $this->_smartyOptions['cache_dir'];
        $smarty->debugging = $this->_smartyOptions['debugging'];
        foreach ($modelAndView->getModel() as $key => $value) {
            $smarty->assign($key, $value);
        }
        $smarty->display(basename($view->getPath()));
    }
}
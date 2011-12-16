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
 * @license    http://marcelog.github.com/ Apache License 2.0
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
namespace Ding\Mvc\Http;

use Ding\Mvc\View;
use Ding\Mvc\ModelAndView;

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
class HttpView extends View
{
    /**
     * Full absolute path for this view.
     * @var string
     */
    private $_path;

    /**
     * Returns path for this view.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
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
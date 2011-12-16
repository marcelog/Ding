<?php
/**
 * Http view resolver. It will try to map the given action name to a full path
 * in the filesystem, accoding to the path, prefix, and suffix configured.
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
use Ding\Logger\ILoggerAware;
use Ding\Mvc\IViewResolver;
use Ding\Mvc\ModelAndView;

/**
 * Http view resolver. It will try to map the given action name to a full path
 * in the filesystem, accoding to the path, prefix, and suffix configured.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class HttpViewResolver implements IViewResolver, ILoggerAware
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Path where views are stored.
     * @var string
     */
    private $_path;

    /**
     * Views prefix.
     * @var string
     */
    private $_prefix;

    /**
     * Views suffix.
     * @var string
     */
    private $_suffix;

    /**
     * This will resolve the given ModelAndView to a view in the filesystem
     * (an absolute path to a file).
     *
     * @param ModelAndView $modelAndView What to render.
     *
     * @see Ding\Mvc.IViewResolver::resolve()
     * @return HttpView
     */
    public function resolve(ModelAndView $modelAndView)
    {
        $name = $modelAndView->getName();
        $path = $this->_path .
            DIRECTORY_SEPARATOR .
            $this->_prefix . $name . $this->_suffix
        ;
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('Using viewpath: ' . $path);
        }
        return new HttpView($modelAndView, $path);
    }

    /**
     * Sets the path where views are located.
     *
     * @param string $path Path to the views directory.
     *
     * @return void
     */
    public function setViewPath($path)
    {
        $len = strlen($path) - 1;
        if ($path[$len] == '/') {
            $path = substr($path, 0, -1);
        }
        $realpath = realpath($path);;
        $this->_path = $realpath === false ? $path : $realpath;
    }

    /**
     * Sets the view prefix, like view.
     *
     * @param string $prefix Prefix to use for views.
     *
     * @return void
     */
    public function setViewPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Logger.ILoggerAware::setLogger()
     */
    public function setLogger(\Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Sets the view suffix, like .html
     *
     * @param string $suffix Suffix to use for views.
     *
     * @return void
     */
    public function setViewSuffix($suffix)
    {
        $this->_suffix = $suffix;
    }
}
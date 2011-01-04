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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\MVC\Http;

use Ding\MVC\IViewResolver;
use Ding\MVC\ModelAndView;

/**
 * Http view resolver. It will try to map the given action name to a full path
 * in the filesystem, accoding to the path, prefix, and suffix configured.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class HttpViewResolver implements IViewResolver
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
     * @see Ding\MVC.IViewResolver::resolve()
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
            $path = substr($path, 0, $len);
        }
        $this->_path = realpath($path);
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
     * Sets the view suffix, like .html
     *
     * @param string $suffix Suffix to use for views.
     * 
     * @return void
     */
    public function setViewSuffix($suffix)
    {
        $this->_logger = \Logger::getLogger('Ding.MVC');
        $this->_suffix = $suffix;
    }
}
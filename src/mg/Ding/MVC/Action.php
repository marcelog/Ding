<?php
/**
 * Abstract action.
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
 * Abstract action.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
abstract class Action
{
    /**
     * Action name, or id, url, path, etc.
     * @var string
     */
    private $_id;
    
    /**
     * Arguments to this action invocation.
     * @var array
     */
    private $_arguments;
    
    /**
     * Returns action id/name.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Returns action arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }
    
    /**
     * Constructor.
     *
     * @param string $id        Action name/id/path/url/etc.
     * @param array  $arguments Action arguments.
     * 
     * @return void
     */
    protected function __construct($id, array $arguments = array())
    {
        $this->_id = $id;
        $this->_arguments = $arguments;
    }
} 
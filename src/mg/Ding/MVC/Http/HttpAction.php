<?php
/**
 * An http action. It adds a method (get, post, etc).
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

use Ding\MVC\Action;

/**
 * An http action. It adds a method (get, post, etc).
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
class HttpAction extends Action
{
    /**
     * Method used to trigger this action.
     * @var string
     */
    private $_method;
    
    /**
     * Sets the method to trigger this action.
     *
     * @param string $method Method used to trigger this action.
     * 
     * @return void
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }
    
    /**
     * Returns the method to trigger this action.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }
    
    /**
     * Constructor.
     *
     * @param string $id        Url.
     * @param array  $arguments Arguments posted (or getted :P).
     * 
     * @return void
     */
    public function __construct($id, array $arguments = array())
    {
        parent::__construct($id, $arguments);
        $this->_method = 'GET';
    }
}
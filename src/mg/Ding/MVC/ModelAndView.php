<?php
/**
 * This class handles the mapping between the view and model.
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
 * This class handles the mapping between the view and model.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class ModelAndView
{
    /**
     * Objects from the model.
     * @var mixed[]
     */
    private $_objects;
    
    /**
     * Model-View name. This will get used to find the view name.
     * @var string
     */
    private $_name;

    /**
     * Add model objects. 
     * 
     * @param array $objects Key = object name (string), value is mixed.
     * 
     * @return void
     */
    public function add(array $objects)
    {
        foreach ($objects as $name => $value) {
            $this->_objects[$name] = $value;
        }
    }
    
    /**
     * Returns the model. Intended to be used from the view.
     * Key = object name (string), value is mixed.
     * 
     * @return array
     */
    public function getModel()
    {
        return $this->_objects;
    }
    
    /**
     * Returns the corresponding view name for this model-view.
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
     * @param string $name    Model-View name.
     * @param array  $options Model-View objects.
     * 
     * @return void
     */
    public function __construct($name, array $options = array())
    {
        $this->_objects = $options;
        $this->_name = $name;
    }
}
<?php
/**
 * This class handles the mapping between the view and model.
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
namespace Ding\Mvc;

/**
 * This class handles the mapping between the view and model.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
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
     * True if this model has no objects.
     *
	 * @return boolean
     */
    public function isEmpty()
    {
        return count($this->_objects) == 0;
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
<?php
/**
 * This will search&replace properties in the form ${property}
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Filter
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
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
namespace Ding\Bean\Factory\Filter;

/**
 * This will search&replace properties in the form ${property}
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Filter
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class PropertyFilter implements IFilter
{
    /**
     * This is a singleton, this is our instance.
     * @var PropertyFilter
     */
    private static $_instance;

    /**
     * Properties.
     * @var array
     */
    private $_properties;

    /**
     * Search and replaces for ${property}
     *
     * @see Ding\Bean\Factory\Filter.IFilter::apply()
     *
     * @return string
     */
    public function apply($input)
    {
        $output = $input;
        if (!is_string($input)) {
            return $input;
        }
        foreach ($this->_properties as $k => $v) {
            if (!is_array($v) && !is_object($v) && (strpos($output, $k) !== false)) {
                $output = str_replace($k, $v, $output);
            }
        }
        return $output;
    }

    /**
     * Returns true if we know the prop named key.
	 *
     * @param string $key String to look for.
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->_properties['${' . $key . '}']);
    }

    /**
     * Returns a value for the given key. False if none found.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->_properties[$key];
        }
        return false;
    }

    /**
     * Sets the given key=value
	 *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->_properties[$key] = $value;
    }

    /**
     * Returns an instance.
     *
     * @param array $properties Properties to use.
     *
     * @return PropertyFilter
     */
    public static function getInstance(array $properties)
    {
        if (self::$_instance == false) {
            self::$_instance = new PropertyFilter($properties);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @param array $properties Properties to use.
	 *
	 * @return void
     */
    private function __construct(array $properties)
    {
	$this->_properties = array();
        foreach (array_keys($properties) as $key) {
            /* Change keys. 'property' becomes ${property} */
            $propName = '${' . $key . '}';
            $this->_properties[$propName] = $properties[$key];
        }
    }
}

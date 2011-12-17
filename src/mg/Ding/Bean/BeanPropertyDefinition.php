<?php
/**
 * Bean property definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
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
namespace Ding\Bean;

/**
 * Bean property definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class BeanPropertyDefinition
{
    /**
     * This constant represents a property that is an integer, string, or any
     * other native type.
     * @var integer
     */
    const PROPERTY_SIMPLE = 0;

    /**
     * This constant represents a property that is another bean.
     * @var integer
     */
    const PROPERTY_BEAN = 1;

    /**
     * This constant represents a property that is an array.
     * @var integer
     */
    const PROPERTY_ARRAY = 2;

    /**
     * This constant represents a property that is php code to be evaluated.
     * @var integer
     */
    const PROPERTY_CODE = 3;

    /**
     * Property name
     * @var string
     */
    private $_name;

    /**
     * Property value (in the case of a bean property, this is the bean name).
     * @var string
     */
    private $_value;

    /**
     * Property type (see this class constants)
     * @var string
     */
    private $_type;

    /**
     * Returns true if this property is a reference to another bean.
     *
     * @return boolean
     */
    public function isBean()
    {
        return $this->_type == self::PROPERTY_BEAN;
    }

    /**
     * Returns true if this property is php code.
     *
     * @return boolean
     */
    public function isCode()
    {
        return $this->_type == self::PROPERTY_CODE;
    }

    /**
     * Returns true if this property is an array.
     *
     * @return boolean
     */
    public function isArray()
    {
        return $this->_type == self::PROPERTY_ARRAY;
    }

    /**
     * Returns property value (or bean name in the case of a bean property).
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Returns property name
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
     * @param string  $name  Target property name.
     * @param integer $type  Target property type (See this class constants).
     * @param string  $value Target property value.
     *
     * @return void
     */
    public function __construct($name, $type, $value)
    {
        $this->_name = $name;
        $this->_type = $type;
        $this->_value = $value;
    }
}

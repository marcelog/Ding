<?php
/**
 * Bean constructor argument definition.
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
 * Bean constructor argument definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class BeanConstructorArgumentDefinition
{
    /**
     * Means this argument is a reference to another bean.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_BEAN = 0;

    /**
     * Means this argument is a literal value.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_VALUE = 1;

    /**
     * Means this argument is an array.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_ARRAY = 2;

    /**
     * Means this argument is php code to be evaluated.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_CODE = 3;

    /**
     * Argument type.
     * @var integer
     */
    private $_type;

    /**
     * Optional argument name.
     * @var string
     */
    private $_name;

    /**
     * Returns value for this argument. This is a bean name (string) in the
     * case of an argument of type bean. If this argument is an array, the
     * value is a BeanConstructorArgument[] where the key of the array is the
     * name of the key for the target array.
     * @var mixed
     */
    private $_value;

    /**
     * Returns argument value. This is a bean name (string) in the
     * case of an argument of type bean.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Returns true if this argument is a reference to another bean.
     *
     * @return boolean
     */
    public function isBean()
    {
        return $this->_type == self::BEAN_CONSTRUCTOR_BEAN;
    }

    /**
     * Returns true if this argument is php code to be evaluated.
     *
     * @return boolean
     */
    public function isCode()
    {
        return $this->_type == self::BEAN_CONSTRUCTOR_CODE;
    }

    /**
     * Returns true if this argument is an array.
     *
     * @return boolean
     */
    public function isArray()
    {
        return $this->_type == self::BEAN_CONSTRUCTOR_ARRAY;
    }

    public function hasName()
    {
        return $this->_name !== false;
    }

    public function getName()
    {
        return $this->_name;
    }
    /**
     * Constructor.
     *
     * @param integer $type  Argument type.
     * @param mixed   $value Argument value.
     *
     * @return void
     */
    public function __construct($type, $value, $name = false)
    {
        $this->_name = $name;
        $this->_type = $type;
        $this->_value = $value;
    }
}

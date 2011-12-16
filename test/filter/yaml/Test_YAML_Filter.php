<?php
/**
 * This class will test the filters with the YAML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Filter.Yaml
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

use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the filters with the YAML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Filter.Yaml
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_YAML_Filter extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    /**
     * @test
     */
    public function can_use_properties()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'filter-xml-simple.yaml', 'directories' => array(RESOURCES_DIR)
                        )
                    ),
                    'properties' => array(
                        'a.b.value' => 'this is a value'
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBean');
        $this->assertEquals($bean->constructor, 'this is a value');
        $this->assertEquals($bean->value, 'this is a value');
    }

}

class ClassSimpleYAMLFilter
{
    public $constructor;
    public $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function __construct($a)
    {
        $this->constructor = $a;
    }
}
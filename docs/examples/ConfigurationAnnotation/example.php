<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage ConfigurationAnnotation
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
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg')),
            ini_get('include_path'),
        )
    )
);

/**
 * @Configuration
 */
class SomeOtherProviderClass
{
    /**
     * @Bean(class=Dependency)
     * @Scope(value=singleton)
     */
    public function dependency()
    {
        return new Dependency();
    }
}
/**
 * @Configuration
 */
class SomeBeanProviderClass
{
    /**
     * @Resource
     */
    protected $dependency;

    /**
     * Notice how the name= here replaces the bean name that comes from the method.
     * This bean should be called someBean (if no name= arguments where passed to the
     * bean annotation.)
     *
     * @Bean(name=someBeanRenamed,class=MyBean)
     * @Scope(value=prototype)
     * @InitMethod(method=aMethod)
     * @DestroyMethod(method=bMethod)
     * @Value(value="arg1")
     * @Value(value="arg2")
     */
    public function someBean($arg1, $arg2)
    {
        $ret = new MyBean($this->dependency, $arg1, $arg2);
        $ret->setSomeProperty('hello world');
        return $ret;
    }
}

class Dependency
{

}

/**
 * This is our bean.
 * @author SomeAuthor
 */
class MyBean
{
    private $_someProperty;
    private $_dependency;
    private $_arg1;
    private $_arg2;

    public function aMethod()
    {
        echo "Init\n";
    }

    public function bMethod()
    {
        echo "Destroy\n";
    }

    public function setSomeProperty($value)
    {
        $this->_someProperty = $value;
    }

    public function getSomeProperty()
    {
        return $this->_someProperty;
    }

    public function getDependency()
    {
        return $this->_dependency;
    }
    public function __construct($dependency, $arg1, $arg2)
    {
        $this->_dependency = $dependency;
        $this->_arg1 = $arg1;
        $this->_arg2 = $arg2;
    }
}
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Reflection\ReflectionFactory;

// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => __DIR__ . '/../log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
        ),
    )
);
$container = ContainerImpl::getInstance($properties);
$bean = $container->getBean('someBeanRenamed');
var_dump($bean);
var_dump($bean->getSomeProperty());
var_dump($bean->getDependency());

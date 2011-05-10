<?php
/**
 * Example using ding. See also beans.yaml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Basic
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
            ini_get('include_path'),
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg'))
        )
    )
);
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.

use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;

class MyOtherBean
{
    public static function getInstance()
    {
        echo "Creating MyOtherBean instance\n";
        return new MyOtherBean();
    }
}

class MyInnerBean
{

}

class AspectA
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before1: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After\n";
    }

    public function __construct()
    {
    }
}

class AspectB extends AspectA
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before2: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After\n";
    }

    public function __construct()
    {
    }
}

/**
 * This is our bean.
 */
class MyBean
{
    public function targetMethod()
    {
        var_dump(func_get_args());
    }
    public function aMethod()
    {
    }

    public function bMethod()
    {

    }

    public function init()
    {
        echo "Init method\n";
    }

    public function destroy()
    {
        echo "Destroy method\n";
    }

    public function setProperty1($value)
    {
        echo "Property1: \n";
        var_dump($value);
    }

    public function setProperty2($value)
    {
        echo "Property2: \n";
        var_dump($value);
    }

    public function setProperty3($value)
    {
        echo "Property3: \n";
        var_dump($value);
    }

    public function setProperty4($value)
    {
        echo "Property4: \n";
        var_dump($value);
    }

    public function setProperty5($value)
    {
        echo "Property5: \n";
        var_dump($value);
    }

    public function setProperty6($value)
    {
        echo "Property6: \n";
        var_dump($value);
    }

    public function setProperty7($value)
    {
        echo "Property7: \n";
        var_dump($value);
    }

    public function setProperty8($value)
    {
        echo "Property8: \n";
        var_dump($value);
    }

    public function setProperty9($value)
    {
        echo "Property9: \n";
        var_dump($value);
    }

    public function setProperty10($value)
    {
        echo "Property10: \n";
        var_dump($value);
    }

    public function __construct()
    {
        echo "Constructor called: \n";
        var_dump(func_get_args());
    }
}

// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'yaml' => array('filename' => 'beans.yaml'),
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
        ),
        // You can configure the cache for the bean definition, the beans, and the proxy definitions.
        // Other available implementations: zend, file, dummy, and memcached.
    	'cache' => array(
            'proxy' => array('impl' => 'dummy'),
            'bdef' => array('impl' => 'dummy'),
            'beans' => array('impl' => 'dummy'),
            'aspect' => array('impl' => 'dummy'),
            'autoloader' => array('impl' => 'dummy')
        )
    )
);
$container = ContainerImpl::getInstance($properties);
$bean = $container->getBean('myBeanName');
var_dump($bean->aMethod());
$bean->targetMethod();

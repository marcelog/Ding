<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Aop
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
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

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
declare(ticks=1);
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
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;

error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class ComponentA
{
    public function getA($a, $b, $c)
    {
        echo "Hello world $a $b $c \n";
    }

    public function __construct()
    {
    }
}

class ComponentB
{
    public function getA($a, $b, $c)
    {
        echo "Hello world $a $b $c \n";
    }

    public function __construct()
    {
    }
}

/**
 * @Aspect
 */
class AspectA
{
    /**
     * @MethodInterceptor(class-expression=C.+,expression=g.+)
     */
    public function invoke(MethodInvocation $invocation)
    {
        try
        {
            echo "Before: " . $invocation->getOriginalInvocation() . "\n";
            $invocation->proceed(array('b', 'c', 'd'));
            echo "After\n";
        } catch(Exception $e) {
            echo "Move along, nothing happened here.. \n";
        }
    }

    public function __construct()
    {
    }
}

class AspectB
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "With exception: " . $invocation->getException() . "\n";
        echo "After with exception\n";
        $invocation->proceed();
    }

    public function __construct()
    {
    }
}
////////////////////////////////////////////////////////////////////////////////
try
{
    $properties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
            'factory' => array(
                'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                    'annotation' => array('scanDir' => array(realpath(__DIR__)))
                ),
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'dummy'),
            	'bdef' => array('impl' => 'dummy'),
              	'beans' => array('impl' => 'dummy'),
                'aspect' => array('impl' => 'dummy')
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    $bean = $a->getBean('ComponentA');
    $bean->getA('a', 1, array('1', 'a'));
    $bean = $a->getBean('ComponentB');
    $bean->getA('a', 1, array('1', 'a'));
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

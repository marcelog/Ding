<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  global
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

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
declare(ticks=1);
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
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;
use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;

// Uncomment these two lines if you want to try zend_cache instead of
// the default available cache backends. Also, modify one of the 'impl' options
// below to use it (see example below).
//require_once 'Zend/Loader/Autoloader.php';
//Zend_Loader_Autoloader::getInstance();

////////////////////////////////////////////////////////////////////////////////
// Normal operation follows...
////////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class ClassA extends ClassD
{
    private $_aComponent;

    public function setAComponent($value)
    {
        $this->_aComponent = $value;
    }

    public function getAComponent()
    {
        return $this->_aComponent;
    }

    public function targetMethod($a, $b, $c)
    {
        echo "Hello world $a $b $c \n";
    }

    /**
     * @anAnnotationB
     */
    public function setAProperty(array $value)
    {
        echo "setting a property: ";
        var_dump($value);
        $this->_aComponent = $value;
    }

    public function __construct(array $a)
    {
        echo "ClassA constructor: ";
        var_dump(func_get_args());
        var_dump($a[100]);
    }
}

class ClassD
{
    public function __construct()
    {
    }
}

class ClassB
{
    private $_aProperty;
    private $_bProperty;

    public function init()
    {
        echo "Initializing class b\n";
    }

    public function shutdown()
    {
        echo "*** Shutting down class b ***\n";
    }

    public function setAProperty($value)
    {
        $this->_aProperty = $value;
    }

    public function getAProperty()
    {
        return $this->_aProperty;
    }

    public function setBProperty($value)
    {
        $this->_bProperty = $value;
    }

    public function getBProperty()
    {
        return $this->_bProperty;
    }

    public function targetMethod()
    {
        throw new Exception('Pepe');
    }
    public function __construct()
    {
        echo "ComponentB constructor called with args: \n";
        var_dump(func_get_args());
    }
}

class ClassC
{

    public function __construct()
    {
    }
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

class AspectB
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before2: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After2\n";
    }

    public function __construct()
    {
    }
}
class AspectC
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before9: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
        $invocation->proceed(func_get_args());
        echo "After9\n";
    }

    public function __construct()
    {
    }
}
class AspectD
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before4: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After4\n";
    }

    public function setAComponent(ClassX $a = null) {

    }
    public function __construct()
    {
    }
}
class AspectE
{
    public function invoke(MethodInvocation $invocation)
    {
        try
        {
            echo "Before3: " . print_r($invocation->getOriginalInvocation(), true) . "\n";
            $invocation->proceed(array('b', 'c', 'd'));
            echo "After3\n";
        } catch(Exception $e) {
            echo "Move along, nothing happened here.. \n";
        }
    }

    public function __construct()
    {
    }
}

class ClassX
{
    public function __construct($a, $b)
    {
        echo "Creating ClassX with args:\n";
        var_dump(func_get_args());
   }
}

class ClassZ
{
    public static function getInstance($a, $b)
    {
        return new ClassX($a, $b);
    }
    public function __construct()
    {
        echo "Creating ClassZ\n";
   }
}

/**
 * @aClassAnnotation
 *
 */
class ClassY
{
    /**
     * @anAnnotationA(A=b, C=a)
     * @anotherAnnotation
     * @YetAnotherOne(a=h)
     * @Aspect
     */
    public static function getInstance($a, $b)
    {
        return new ClassY($a, $b);
    }

    private function __construct($a, $b)
    {
        echo "Creating ClassY with args:\n";
        var_dump(func_get_args());
    }
}
////////////////////////////////////////////////////////////////////////////////
try
{
    $zendCacheOptions = array(
        'frontend' => 'Core',
        'backend' => 'File',
        'backendoptions' => array('cache_dir' => '/tmp/Ding/zend/cache'),
        'frontendoptions' => array('lifetime' => 10000, 'automatic_serialization' => true)
    );
    $memcachedOptions = array('host' => '127.0.0.1', 'port' => 11211);
    $properties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
            'factory' => array(
                'properties' => array('configDir' => __DIR__),
               'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                    'annotation' => array('scanDir' => array(realpath(__DIR__)))
                ),
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/proxy'),
//        		'bdef' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
//              'bdef' => array('impl' => 'apc'),
//              'bdef' => array('impl' => 'dummy'),
                'aspect' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/aspect'),
            	'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/bdef'),
        		//'beans' => array('impl' => 'file', 'directory' => '/tmp/Ding/beans'),
//        		'bdef' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
//        		'beans' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
              	'beans' => array('impl' => 'dummy')
//              'beans' => array('impl' => 'apc')
//        		'beans' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    $bean = $a->getBean('ComponentA');
    $bean->targetMethod('a', 1, array('1' => '2'));
    $bean = $a->getBean('ComponentB');
    $bean->targetMethod('a', 1, array('1' => '2'));

    $bean = $a->getBean('ComponentY');
    $bean = $a->getBean('ComponentX');
    $triggererror = $asd;
    posix_kill(posix_getpid(), SIGUSR1);
    sleep(5);
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

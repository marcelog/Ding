<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  global
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
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
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;
use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;
use Ding\Aspect\Interceptor\IMethodInterceptor;
use Ding\Aspect\Interceptor\IExceptionInterceptor;

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

class AspectA implements IMethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before1: " . $invocation->getOriginalInvocation() . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After\n";
    }

    public function __construct()
    {
    }
}

class AspectB implements IMethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before2: " . $invocation->getOriginalInvocation() . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After2\n";
    }

    public function __construct()
    {
    }
}
class AspectC implements IMethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before9: " . $invocation->getOriginalInvocation() . "\n";
        $invocation->proceed(func_get_args());
        echo "After9\n";
    }

    public function __construct()
    {
    }
}
class AspectD implements IExceptionInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Before4: " . $invocation->getOriginalInvocation() . "\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After4\n";
    }

    public function setAComponent(ClassX $a = null) {

    }
    public function __construct()
    {
    }
}
class AspectE implements IMethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        try
        {
            echo "Before3: " . $invocation->getOriginalInvocation() . "\n";
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
                'drivers' => array(
                    'signalhandler' => array(),
		    		'shutdown' => array(),
                    'timezone' => array(),
			    	'errorhandler' => array()
                ),
               'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                    'annotation' => array('scanDir' => array(realpath(__DIR__)))
                ),
                'properties' => array(
                    'user.name' => 'nobody',
                    'log.dir' => '/tmp/alogdir',
                    'log.file' => 'alog.log',
                    'timezone' => 'America/Buenos_Aires'
                )
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'file', 'directory' => '/tmp/Ding/proxy'),
//        		'bdef' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
//              'bdef' => array('impl' => 'apc'),
//              'bdef' => array('impl' => 'dummy'),
            	'bdef' => array('impl' => 'file', 'directory' => '/tmp/Ding/bdef'),
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

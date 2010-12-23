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
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            implode(DIRECTORY_SEPARATOR, array('src', 'mg'))
        )
    )
);
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;
use Ding\Aspect\Interceptor\IMethodInterceptor;
use Ding\Aspect\Interceptor\IExceptionInterceptor;

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

class ClassY
{
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
    $properties = array(
        'user.name' => 'nobody',
        'log.dir' => '/tmp/alogdir',
        'log.file' => 'alog.log'
    );
    $a = ContainerImpl::getInstanceFromXml('beans.xml', $properties);
    $bean = $a->getBean('ComponentA');
    $bean->targetMethod('a', 1, array('1' => '2'));
    $bean = $a->getBean('ComponentB');
    $bean->targetMethod('a', 1, array('1' => '2'));
    
    $bean = $a->getBean('ComponentY');
    $bean = $a->getBean('ComponentX');
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

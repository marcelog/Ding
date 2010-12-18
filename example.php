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
    
}

class ClassD {
    
}

class ClassB
{
    private $_aProperty;
    private $_bProperty;
    
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
}

class ClassC
{
}

class AspectA
{
    public function anAdvice(MethodInvocation $invocation)
    {
        echo "Before\n";
        $invocation->proceed(array('b', 'c', 'd'));
        echo "After\n";
    }
}
////////////////////////////////////////////////////////////////////////////////
try
{
    $a = ContainerImpl::getInstanceFromXml('beans.xml');
    $bean = $a->getBean('ComponentA');
    $bean->targetMethod('a', 1, array('1' => '2'));
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

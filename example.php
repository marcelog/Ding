<?php
/**
 * Example using ding. See also beans.xml.
 * 
 * Run this like: /usr/bin/php -d include_path=.:src/mg/ding example.php
 *
 * PHP Version 5
 *
 * @category ding
 * @package  global
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
require_once 'autoloader/Autoloader.php'; // Include ding autoloader.
Autoloader::register(); // Call autoloader register for ding autoloader.

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
    public function anAdvice(Ding\MethodInvocation $invocation)
    {
        echo "Aspected!\n";
    }
}
////////////////////////////////////////////////////////////////////////////////
try
{
    $a = Ding\ContainerImpl::getInstance('beans.xml');
    $bean = $a->getBean('ComponentA');
    $bean->targetMethod('a', 1, array('1' => '2'));
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

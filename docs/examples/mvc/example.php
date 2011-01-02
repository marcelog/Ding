<?php
/**
 * Example using ding mvc.
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
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg'))
        )
    )
);
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
////////////////////////////////////////////////////////////////////////////////
// Normal operation follows... 
////////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class MyController
{
    public function someAction(array $arguments)
    {
        
    }
}

try
{
    $properties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
            'factory' => array(
                'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                	'annotation' => array()
                ),
                'properties' => array(
                    'viewPath' => './',
                    'viewSuffix' => '.html',
                    'viewPrefix' => 'view.',
                )
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'file', 'directory' => '/tmp/Ding/proxy'),
        		'bdef' => array('impl' => 'file', 'directory' => '/tmp/Ding/bdef'),
        		'beans' => array('impl' => 'file', 'directory' => '/tmp/Ding/beans'),
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    $bean = $a->getBean('HttpDispatcher');
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

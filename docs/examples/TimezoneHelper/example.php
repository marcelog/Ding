<?php
/**
 * Example using TimezoneHandler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage TimezoneHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;

error_reporting(E_ALL);
ini_set('display_errorrs', 1);

// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'xml' => array('filename' => 'beans.xml'),
            ),
            // These properties will be used by the container when instantiating the beans, see beans.xml
            'properties' => array(
                'timezone' => 'Arctic/Longyearbyen',
            )
        ),
        // You can configure the cache for the bean definition, the beans, and the proxy definitions.
        // Other available implementations: zend, file, dummy, and memcached.
    	'cache' => array(
            'proxy' => array('impl' => 'dummy'),
            'bdef' => array('impl' => 'dummy'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
$container = ContainerImpl::getInstance($properties);

var_dump(date_default_timezone_get());

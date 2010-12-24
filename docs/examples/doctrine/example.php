<?php
/**
 * Example using Ding with Doctrine.
 * 
 * PHP Version 5
 *
 * @category Ding
 * @package  
 * @author   Agustín Gutiérrez <agu.gutierrez@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

// no php notices please...
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);


/**
 * Note: make sure Doctrine library is defined in your include path.
 */
define('DOCTRINE_LIB_PATH', '/usr/php-5.3/lib/php');
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            DOCTRINE_LIB_PATH,
            __DIR__ .'/entities',
            ini_get('include_path'),
            __DIR__ .DIRECTORY_SEPARATOR
            .implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg')),
        )
    )
);

// register Doctrine class loader
require 'Doctrine/Common/ClassLoader.php';
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine', DOCTRINE_LIB_PATH);
$classLoader->register();

// register Ding autoloader
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
Autoloader::register(); // Call autoloader register for ding autoloader.

use Ding\Container\Impl\ContainerImpl;
use Doctrine\ORM\EntityManager;
try
{
    $properties = array(
        'doctrine.proxy.dir' => './proxies',
        'doctrine.proxy.autogenerate' => true,
        'doctrine.proxy.namespace' => "\\Test\\Proxies",
        'doctrine.entity.path' => "./entities",
        'doctrine.db.driver' => "pdo_sqlite",
        'doctrine.db.path' => "database.sqlite3",
        'user.name' => 'nobody',
        'log.dir' => '/tmp/alogdir',
        'log.file' => 'alog.log',
        'ding.cache.impl' => 'dummy' // You may use 'apc' here
    );

    $a = ContainerImpl::getInstanceFromXml('beans.xml', $properties);
    $em = $a->getBean('repository-locator');
    require_once 'entities/Person.php';
    $person = new Person('foobar', 'Foo', 'Bar');
    echo "Persisting $person\n";
    $em->persist($person);
    $em->flush();
    $person = $em->find('Person', 1);

    echo "Retrieved from db:$person\n";

} catch(Exception $exception) {
    echo $exception . "\n";
}

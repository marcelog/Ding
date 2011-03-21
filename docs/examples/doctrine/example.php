<?php
/**
 * Example using Ding with Doctrine.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package
 * @author   Agustín Gutiérrez <agu.gutierrez@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @version  SVN: $Id$
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

require_once 'entities/Person.php';

// register Doctrine class loader
require 'Doctrine/Common/ClassLoader.php';
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine', DOCTRINE_LIB_PATH);
$classLoader->register();

// register Ding autoloader
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.

// Uncomment these two lines if you want to try zend_cache instead of
// the default available cache backends. Also, modify one of the 'impl' options
// below to use it (see example below).
//require_once 'Zend/Loader/Autoloader.php';
//Zend_Loader_Autoloader::getInstance();

use Ding\Container\Impl\ContainerImpl;
use Doctrine\ORM\EntityManager;
try
{
    $myProperties = array(
        'doctrine.proxy.dir' => './proxies',
        'doctrine.proxy.autogenerate' => true,
        'doctrine.proxy.namespace' => "\\Test\\Proxies",
        'doctrine.entity.path' => __DIR__ ."/entities",
        'doctrine.db.driver' => "pdo_sqlite",
        'doctrine.db.path' => __DIR__ ."/db.sqlite3",
        'user.name' => 'nobody',
        'log.dir' => '/tmp/alogdir',
        'log.file' => 'alog.log'
	 );
    $dingProperties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
    		'factory' => array(
                'bdef' => array(
                	'xml' => array('filename' => 'beans.xml', 'directories' => array(__DIR__)),
                ),
                'properties' => $myProperties
            ),
    		  'cache' => array(
    			'proxy' => array('impl' => 'dummy', 'directories' => '/tmp/Ding/proxy'),
//        		'bdef' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
//              'bdef' => array('impl' => 'apc'),
        		'bdef' => array('impl' => 'dummy', 'directories' => '/tmp/Ding/bdef'),
//        		'beans' => array('impl' => 'file', 'directory' => '/tmp/Ding/beans'),
//        		'bdef' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
//        		'beans' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
                'beans' => array('impl' => 'dummy')
//              'beans' => array('impl' => 'apc')
//        		'beans' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
           )
        )
    );
    $a = ContainerImpl::getInstance($dingProperties);
    $em = $a->getBean('repository-locator');
    createSchema($myProperties);

    $person = new Person('foobar', 'Foo', 'Bar');
    echo "Persisting $person\n";
    $em->persist($person);
    $em->flush();
    $person = $em->find('Person', 1);

    echo "Retrieved from db:$person\n";

    @unlink($myProperties['doctrine.db.path']);
} catch(Exception $exception) {
    echo $exception . "\n";
}

function createSchema($props) {
    $schema = file_get_contents(__DIR__ .'/schema.sql');
    $config = new \Doctrine\DBAL\Configuration();
    //..
    $connectionParams = array(
        'driver' => $props['doctrine.db.driver'],
        'path' => $props['doctrine.db.path'],
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    $conn->executeQuery($schema);
}

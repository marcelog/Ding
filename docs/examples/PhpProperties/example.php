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
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/proxy'),
                'aspect' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/aspect'),
            	'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/cache/bdef'),
              	'beans' => array('impl' => 'dummy')
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    var_dump(ini_get('date.timezone'));
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

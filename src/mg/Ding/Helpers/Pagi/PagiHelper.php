<?php
/**
 * This is our "dispatcher", you should invoke this one from your dialplan.
 * This script will assume the existance of the following environment variables:
 * -- PAGIApplication: Name of your application's class.
 * -- PAGIBootstrap: Name of the file (like a.php) that you want to include_once
 * before running the application.
 * -- log4php: Absolute full path to the log4php.properties (may be a dummy
 * path, in this case you may gain some performance but wont be able to see
 * any logs apart from the asterisk console).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pagi
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
require_once 'Ding/Autoloader/Autoloader.php';
require_once 'PAGI/Autoloader/Autoloader.php';
\Ding\Autoloader\Autoloader::register();
\PAGI\Autoloader\Autoloader::register();


use PAGI\Application\Exception\InvalidApplicationException;
use Ding\Container\Impl\ContainerImpl;
use PAGI\Client\Impl\ClientImpl;

try
{
    $bootstrap = getenv('PAGIBootstrap');
    include_once $bootstrap;
    $log4php = getenv('log4php_properties');
    $agiLogger = false;
    $agi = ClientImpl::getInstance(array('log4php.properties' => $log4php));
    $agiLogger = $agi->getAsteriskLogger();
    $container = ContainerImpl::getInstance($properties);
    $extension = $agi->getChannelVariables()->getDNIS();
    $mapper = $container->getBean('PagiExtensionMapper');
    $myApp = $mapper->resolve($extension);
    $agi->consoleLog('Launching ' . get_class($myApp));
    if ($myApp === false) {
        throw new \Exception('No applications found.');
    }
    $myApp->init();
    $myApp->run();
} catch (\Exception $e) {
    $agiLogger->error($e);
}

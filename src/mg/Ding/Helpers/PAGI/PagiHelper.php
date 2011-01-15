<?php
/**
 * This is our "dispatcher", you should invoke this one from your dialplan.
 * This script will assume the existance of the following environment variables:
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
 * @subpackage PAGI
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
require_once 'Ding/Autoloader/Ding_Autoloader.php';
require_once 'PAGI/Autoloader/PAGI_Autoloader.php';
PAGI_Autoloader::register();
Ding_Autoloader::register();

use PAGI\Application\Exception\InvalidApplicationException;
use Ding\Container\Impl\ContainerImpl;
use PAGI\Client\Impl\ClientImpl;

$appName = getenv('PAGIApplication');
$bootstrap = getenv('PAGIBootstrap');
$log4php = realpath(getenv('log4php_properties'));
try
{
    include_once $bootstrap;
    $container = ContainerImpl::getInstance($properties);
    $agi = ClientImpl::getInstance(array('log4php.properties' => $log4php));
    $extension = $agi->getChannelVariables()->getDNIS();
    $mapper = $container->getBean('PagiExtensionMapper');
    $myApp = $mapper->resolve($extension);
    $agi->log('Launching ' . get_class($myApp));
    if ($myApp === false) {
        throw new \Exception('No applications found.');
    }
    $myApp->init();
    $myApp->run();
} catch (\Exception $e) {
    $agi->log($e);
}

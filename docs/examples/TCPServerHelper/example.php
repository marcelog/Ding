<?php
/**
 * TCP Client example.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage TCPServerHelper
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
declare(ticks=1);
require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();
use Ding\Container\Impl\ContainerImpl;

$run = true;
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array(
                'xml' => array('filename' => 'beans.xml'),
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
            'properties' => array(
                'tcp.address' => '127.0.0.1',
                'tcp.port' => 8881,
                'tcp.backlog' => 1, // Max connections
                'tcp.rto' => 10000, // in milliseconds or 0 to disable (never blocks)
                'tcp.rlen' => 1
            )
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy'),
            'bdef' => array('impl' => 'dummy'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
$a = ContainerImpl::getInstance($properties);
$server = $a->getBean('Server');
$server->open();

while($run)
{
    usleep(1000);
}
$server->close();
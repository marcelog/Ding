<?php
/**
 * TCP Client example. Note: It is normal to see messages from the error handler
 * with the message "operation is now in progress" due to the use of non
 * blocking sockets.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage TCPClientHelper
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
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

require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();
use Ding\Container\Impl\ContainerImpl;

$connected = false;
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
                'tcp.cto' => 100, // in milliseconds or 0 to block
                'tcp.rto' => 500, // in milliseconds or 0 to disable (never blocks)
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
$client = $a->getBean('Client');
$client->open();
while(!$connected && $run) {
    usleep(1000);
}

while($run) {
    usleep(1000);
}

<?php
declare(ticks=1);
/**
 * PAMI example.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Pami
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
require_once 'Ding/Autoloader/Ding_Autoloader.php';
require_once 'PAMI/Autoloader/PAMI_Autoloader.php';

Ding_Autoloader::register();
PAMI_Autoloader::register();

use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\PAMI\IPamiEventHandler;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Action\ListCommandsAction;

if ($argc != 5) {
    echo "Use: $argv[0] <host> <port> <user> <pass>\n";
    exit (254);
}

class MyPamiHandler implements IPamiEventHandler
{
    public function handlePamiEvent(EventMessage $event)
    {
        var_dump($event);
    }
}

$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array('xml' => array('filename' => 'beans.xml')),
            'properties' => array(
                'ami.host' => $argv[1],
                'ami.port' => $argv[2],
				'ami.user' => $argv[3],
                'ami.pass' => $argv[4],
            )
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/proxy'),
            'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/bdef'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
$a = ContainerImpl::getInstance($properties);
$ami = $a->getBean('Pami');
var_dump($ami->send(new ListCommandsAction()));
while(true){
    $ami->process();
    usleep(1);
}

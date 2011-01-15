<?php
declare(ticks=1);
/**
 * Syslog example
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Timer
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();

use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\Timer\ITimerHandler;

class MyTimerHandler1 implements ITimerHandler
{
    public function handleTimer()
    {
        echo "hello there every five seconds\n";
    }
}

class MyTimerHandler2 implements ITimerHandler
{
    public function handleTimer()
    {
        echo "hello there every second\n";
    }
}

$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array('xml' => array('filename' => 'beans.xml')),
            'properties' => array(
                'interval1' => 5000,
                'interval2' => 1000
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
$timer1 = $a->getBean('Timer1');
$timer2 = $a->getBean('Timer2');
$timer1->start();
$timer2->start();
while(true){
    usleep(1);
}
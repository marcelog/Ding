<?php
/**
 * TCP Client example.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Tcp
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
declare(ticks=1);
require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();

use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;
use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\TCP\ITCPServerHandler;

class MyErrorHandler implements IErrorHandler, ISignalHandler, IShutdownHandler
{
    public function handleError(ErrorInfo $error)
    {
        echo "This is your custom error handler: " . print_r($error, true);
    }

    public function handleShutdown()
    {
        echo "This is your custom shutdown handler.\n";
    }

    public function handleSignal($signal)
    {
        global $run;
        echo "This is your custom signal handler: " . $signal . "\n";
        $run = false;
    }
}

class MyServerHandler implements ITCPServerHandler
{
    public function beforeOpen()
    {
        echo "before open\n";
    }

    public function beforeListen()
    {
        echo "before listen\n";
    }

    public function close()
    {
        echo "close\n";
    }

    public function handleConnection($remoteAddress, $remotePort)
    {
        global $server;
        echo "new connection from: $remoteAddress:$remotePort\n";
    }

    public function readTimeout($remoteAddress, $remotePort)
    {
        global $server;
        echo "read timeout for $remoteAddress:$remotePort\n";
        $server->disconnect($remoteAddress, $remotePort);
    }

    public function handleData($remoteAddress, $remotePort)
    {
        global $server;
        $buffer = '';
        $len = 4096;
        echo "data from: $remoteAddress:$remotePort\n";
        $server->read($remoteAddress, $remotePort, $buffer, $len);
        echo $buffer . "\n";
        $server->write($remoteAddress, $remotePort, 'You said: ' . $buffer);
    }

    public function disconnect($remoteAddress, $remotePort)
    {
        echo "disconnect: $remoteAddress:$remotePort\n";
    }
}

$run = true;
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array('xml' => array('filename' => 'beans.xml')),
            'properties' => array(
                'timezone' => 'America/Buenos_Aires',
                'tcp.address' => '127.0.0.1',
                'tcp.port' => 8881,
                'tcp.backlog' => 1, // Max connections
                'tcp.rto' => 10000, // in milliseconds or 0 to disable (never blocks)
                'tcp.rlen' => 1
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
$server = $a->getBean('Server');
$server->open();

while($run)
{
    usleep(1000);
}
$server->close();
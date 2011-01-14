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
use Ding\Helpers\TCP\ITCPClientHandler;

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

$connected = false;
class MyClientHandler implements ITCPClientHandler
{

    public function connectTimeout()
    {
        echo "connection timeout\n";
    }

    public function readTimeout()
    {
        global $client;
        echo "read timeout\n";
        $client->close();
    }
    public function beforeConnect()
    {
        echo "before connect\n";
    }

    public function connect()
    {
        global $connected;
        global $client;
        $connected = true;
        echo "connected\n";
        $client->write("GET / HTTP/1.0\n\n");
    }

    public function disconnect()
    {
        global $connected;
        global $run;
        $connected = false;
        echo "disconnected\n";
        $run = false;
    }

    public function data()
    {
        global $client;
        $buffer = '';
        $len = 4096;
        $len = $client->read($buffer, $len);
        echo "got data (" . $len . "): \n";
        echo $buffer . "\n";
        $client->close();
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
                'tcp.port' => 80,
                'tcp.cto' => 100, // in milliseconds or 0 to block
                'tcp.rto' => 500, // in milliseconds or 0 to disable (never blocks)
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
$client = $a->getBean('Client');
$client->open();
while(!$connected && $run) {
    usleep(1000);
}

while($run) {
    usleep(1000);
}

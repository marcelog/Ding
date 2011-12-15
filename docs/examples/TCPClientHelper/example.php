<?php
/**
 * Tcp Client example. Note: It is normal to see messages from the error handler
 * with the message "operation is now in progress" due to the use of non
 * blocking sockets.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage TcpClientHelper
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
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
declare(ticks=1);
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg')),
            ini_get('include_path'),
        )
    )
);

require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\Tcp\ITcpClientHandler;
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;

/**
 * @ErrorHandler
 * @SignalHandler
 * @ShutdownHandler
 */
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

class MyClientHandler implements ITcpClientHandler
{
    protected $client;
    public function connectTimeout()
    {
        echo "connection timeout\n";
    }

    public function readTimeout()
    {
        echo "read timeout\n";
        $this->client->close();
    }
    public function beforeConnect()
    {
        echo "before connect\n";
    }

    public function connect()
    {
        global $connected;
        $connected = true;
        echo "connected\n";
        $this->client->write("GET / HTTP/1.0\n\n");
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
        $buffer = '';
        $len = 4096;
        $len = $this->client->read($buffer, $len);
        echo "got data (" . $len . "): \n";
        echo $buffer . "\n";
        $this->client->close();
    }

    public function setClient(\Ding\Helpers\Tcp\TcpClientHelper $client)
    {
        $this->client = $client;
    }
}

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
$client->open('192.168.0.10', 9991);
while(!$connected && $run) {
    usleep(1000);
}

while($run) {
    usleep(1000);
}

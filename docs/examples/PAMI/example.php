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
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.

require_once 'PAMI/Autoloader/Autoloader.php';
\PAMI\Autoloader\Autoloader::register();

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

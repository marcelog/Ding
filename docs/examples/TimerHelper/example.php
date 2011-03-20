<?php
declare(ticks=1);
/**
 * Syslog example
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage TimerHelper
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
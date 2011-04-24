<?php
declare(ticks=1);
/**
 * This class will test the timer driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Timer
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

use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\Timer\ITimerHandler;

/**
 * This class will test the timer driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Timer
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Timer extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_use_timer()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
        			'drivers' => array('timer' => array()),
                    'bdef' => array(
                        'xml' => array('filename' => 'timer.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $timer = $container->getBean('Timer1');
        $timer->start();
        $start = time();
        while (true) {
            usleep(1);
            if ((time() - $start) >= 5) {
                break;
            }
        }
        $timer->stop();
        $this->assertEquals(MyTimerHandler::$something, 'aaaaa');
    }

}

class MyTimerHandler implements ITimerHandler
{
    public static $something = '';

    public function handleTimer()
    {
        self::$something .= 'a';
    }

}

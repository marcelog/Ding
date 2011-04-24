<?php
/**
 * This class will test the syslog feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Syslog
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
 * This class will test the syslog feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Syslog
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Syslog extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_use_syslog()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
        			'drivers' => array('timer' => array()),
                    'bdef' => array(
                        'xml' => array('filename' => 'syslog.xml', 'directories' => array(RESOURCES_DIR))
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $syslog = $container->getBean('syslog');
        $syslog->open();
        $syslog->emerg('some log');
        $syslog->alert('some log');
        $syslog->critical('some log');
        $syslog->error('some log');
        $syslog->warning('some log');
        $syslog->notice('some log');
        $syslog->info('some log');
        $syslog->debug('some log');
        $syslog->close();
    }
}

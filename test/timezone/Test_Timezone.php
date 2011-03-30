<?php
/**
 * This class will test the timezone driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Timezone
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
use Ding\Aspect\MethodInvocation;

/**
 * This class will test the timezone driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Timezone
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Timezone extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_set_timezone()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
        			'drivers' => array('timezone' => array()),
                    'bdef' => array(
                        'xml' => array('filename' => 'timezoneBeans.xml', 'directories' => array(RESOURCES_DIR))
                    ),
                    'properties' => array('timezone' => 'Navajo')
                )
            )
        );
        // Nothing against the navajo people. Just picked up an unlikely timezone
        // string.
        $oldTimezone = date_default_timezone_get();
        date_default_timezone_set('Navajo');
        $this->assertNotEquals($oldTimezone, 'Navajo');
        $container = ContainerImpl::getInstance($properties);
        $newTimezone = date_default_timezone_get();
        $this->assertNotEquals($oldTimezone, $newTimezone);
        $this->assertEquals($newTimezone, 'Navajo');
    }
}

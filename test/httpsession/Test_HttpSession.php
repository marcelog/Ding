<?php
/**
 * This class will test the HttpSession.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Httpsession
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
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

use Ding\HttpSession\HttpSession;

/**
 * This class will test the HttpSession.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Httpsession
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_HttpSession extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_use_session()
    {
        $session = HttpSession::getSession();
        $session->destroy();
    }

    /**
     * @test
     */
    public function can_return_false_on_invalid_attribute()
    {
        $session = HttpSession::getSession();
        $this->assertFalse($session->getAttribute('notexistant'));
    }

    /**
     * @test
     */
    public function can_use_attributes()
    {
        $session = HttpSession::getSession();
        $session->setAttribute('foo', 'bar');
        $this->assertEquals($session->getAttribute('foo'), 'bar');
        $this->assertTrue($session->hasAttribute('foo'));
    }
}
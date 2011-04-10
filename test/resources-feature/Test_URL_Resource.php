<?php
/**
 * This class will test the Resources feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Resources
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

use Ding\Resource\Impl\URLResource;
use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the Resources feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Resources
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_URL_Resource extends PHPUnit_Framework_TestCase
{
    private $_url;
    private $_filename;
    private $_resourceName;

    public function setUp()
    {
        $this->_resourceName = 'index.html';
        $this->_filename = DIRECTORY_SEPARATOR . $this->_resourceName;
        $this->_url = 'http://www.google.com' . $this->_filename;
    }

    /**
     * @test
     */
    public function can_work_with_and_without_scheme()
    {
        $resource = new URLResource($this->_url);
        $this->assertFalse($resource->isOpen());
        $this->assertTrue($resource->exists());
        $this->assertTrue($resource->isOpen());
        $this->assertEquals($resource->getURL(), $this->_url);
        $this->assertEquals($resource->getFilename(), $this->_filename);
    }

    /**
     * @test
     */
    public function can_open()
    {
        $resource = new URLResource($this->_url);
        $contents = fread($resource->getStream(), 1000);
        $this->assertTrue($resource->isOpen());
    }

    /**
     * @test
     */
    public function can_create_relative()
    {
        $resource = new URLResource('http://www.google.com');
        $this->assertEquals($this->_url, $resource->createRelative($this->_resourceName)->getURL());
    }

    /**
     * @test
     * @expectedException Ding\Resource\Exception\ResourceException
     */
    public function cannot_open_invalid_file()
    {
        $resource = new URLResource('/please/dont/create/this/path/so/this/test/will/work');
    }

    /**
     * @test
     * @expectedException Ding\Resource\Exception\ResourceException
     */
    public function cannot_open_invalid_url()
    {
        $resource = new URLResource('http:///a.com?a?a');
    }

    /**
     * @test
     */
    public function cannot_check_exists_on_invalid_file()
    {
        $resource = new URLResource('file:///please/dont/create/this/path/so/this/test/will/work');
        $this->assertFalse($resource->exists());
    }
}
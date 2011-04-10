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

use Ding\Resource\Impl\FilesystemResource;
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
class Test_Filesystem_Resource extends PHPUnit_Framework_TestCase
{
    private $_url;
    private $_filename;
    private $_resourceName;

    public function setUp()
    {
        $this->_resourceName = 'someresource.txt';
        $this->_filename = RESOURCES_DIR . DIRECTORY_SEPARATOR . $this->_resourceName;
        $this->_url = 'file://' . $this->_filename;
    }

    /**
     * @test
     */
    public function can_work_with_and_without_scheme()
    {
        $resource = new FilesystemResource($this->_filename);
        $resource2 = new FilesystemResource($this->_url);

        $this->assertTrue($resource->exists());
        $this->assertFalse($resource->isOpen());
        $this->assertEquals($resource->getURL(), $this->_url);
        $this->assertEquals($resource->getFilename(), $this->_filename);

        $this->assertEquals($resource->exists(), $resource2->exists());
        $this->assertEquals($resource->isOpen(), $resource2->isOpen());
        $this->assertEquals($resource->getURL(), $resource2->getURL());
        $this->assertEquals($resource->getFilename(), $resource2->getFilename());
    }

    /**
     * @test
     */
    public function can_open()
    {
        $resource = new FilesystemResource($this->_filename);
        $resource2 = new FilesystemResource($this->_url);
        $contents = fread($resource->getStream(), 1000);
        $contents2 = fread($resource2->getStream(), 1000);
        $this->assertEquals($contents, $contents2);
        $this->assertTrue($resource->isOpen());
        $this->assertTrue($resource2->isOpen());
        $this->assertEquals($contents, 'hello world');
    }

    /**
     * @test
     */
    public function can_create_relative()
    {
        $resource = new FilesystemResource(RESOURCES_DIR);
        $this->assertEquals($this->_url, $resource->createRelative($this->_resourceName)->getURL());
    }

    /**
     * @test
     * @expectedException Ding\Resource\Exception\ResourceException
     */
    public function cannot_open_invalid_file()
    {
        $resource = new FilesystemResource('/please/dont/create/this/path/so/this/test/will/work');
        $resource->getStream();
    }
}
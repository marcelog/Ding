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

use Ding\Resource\Impl\IncludePathResource;
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
class Test_IncludePath_Resource extends PHPUnit_Framework_TestCase
{
    private $_url;
    private $_filename;
    private $_resourceName;

    public function setUp()
    {
        $this->_resourceName = 'Autoloader.php';
        $this->_filename = 'Ding' . DIRECTORY_SEPARATOR . 'Autoloader' . DIRECTORY_SEPARATOR . $this->_resourceName;
        $this->_url = 'includepath://' . $this->_filename;
    }

    /**
     * @test
     */
    public function can_work_with_and_without_scheme()
    {
        $resource = new IncludePathResource($this->_filename);
        $resource2 = new IncludePathResource($this->_url);

        $this->assertTrue($resource->exists());
        $this->assertFalse($resource->isOpen());

        $this->assertEquals($resource->exists(), $resource2->exists());
        $this->assertEquals($resource->isOpen(), $resource2->isOpen());
        $this->assertEquals($resource->getURL(), $resource2->getURL());
        $this->assertEquals($resource->getFilename(), $resource2->getFilename());
    }
}
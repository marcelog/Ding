<?php
/**
 * This class will test the dummy cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Dummy
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

use Ding\Cache\Locator\CacheLocator;
use Ding\Container\Impl\ContainerImpl;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Cache\Exception\FileCacheException;

/**
 * This class will test the dummy cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Dummy
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Cache_Dummy extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(
    				'proxy' => array('impl' => 'dummy'),
                	'aspect' => array('impl' => 'dummy'),
            		'bdef' => array('impl' => 'dummy'),
        			'annotations' => array('impl' => 'dummy'),
              		'beans' => array('impl' => 'dummy')
                ),
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'ioc-xml-simple.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    public function tearDown()
    {
        $cache = CacheLocator::getProxyCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getDefinitionsCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getBeansCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getAspectCacheInstance();
        $cache->flush();
    }

    /**
     * @test
     */
    public function can_remove()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $cache = CacheLocator::getProxyCacheInstance();
        $cache->remove('a');
    }
}

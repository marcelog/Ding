<?php
/**
 * This class will test the IResourceLoader feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
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
use Ding\Resource\IResourceLoaderAware;
use Ding\Resource\IResourceLoader;

/**
 * This class will test the IResourceLoader feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_ResourceLoader_Aware extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array(
                        	'scanDir' => array(realpath(__DIR__))
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_be_resource_loader_aware()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleResourceLoaderAwareBean');
        $this->assertTrue($bean->resourceLoader instanceof IResourceLoader);
    }
}

/**
 * @Configuration
 */
class ClassResourceLoaderAwareConfiguration
{
    /**
     * @Bean(class=ClassResourceLoaderAware)
     * @Scope(value=singleton)
     */
    public function aSimpleResourceLoaderAwareBean()
    {
        return new ClassResourceLoaderAware();
    }
}

class ClassResourceLoaderAware implements IResourceLoaderAware
{
    public $resourceLoader = null;

    public function setResourceLoader(IResourceLoader $resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;
    }

    public function __construct()
    {
    }
}

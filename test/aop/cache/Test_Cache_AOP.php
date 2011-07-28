<?php
/**
 * This class will test the aop driver with cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Cache
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

use Ding\Aspect\PointcutDefinition;
use Ding\Aspect\AspectManager;
use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;

/**
 * This class will test the aop driver with cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Cache
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_XML_Cache extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
       $cachedir = implode(DIRECTORY_SEPARATOR, array(getenv('TMPDIR'), 'cache', __CLASS__));
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(
                	'aspect' => array('impl' => 'file', 'directory' => $cachedir)
                ),
        		'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'aop-xml-simple.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_cache_pointcut()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $aManager = new AspectManager;
        $pointcut = new PointcutDefinition('a', 'b', 'c');
        $aManager->setPointcut($pointcut);
        $p = $aManager->getPointcut('a');
        $this->assertEquals($p->getExpression(), 'b');
        $this->assertEquals($p->getMethod(), 'c');
    }

    /**
     * @test
     */
    public function can_return_cached_pointcut()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $aManager = new AspectManager;
        $p = $aManager->getPointcut('a');
        $this->assertEquals($p->getExpression(), 'b');
        $this->assertEquals($p->getMethod(), 'c');
    }
}

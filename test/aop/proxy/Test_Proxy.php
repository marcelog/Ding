<?php
/**
 * This class will test the proxy feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Proxy
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

use Ding\Container\Impl\ContainerImpl;
use Ding\Aspect\MethodInvocation;

/**
 * This class will test the proxy feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Proxy
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Proxy extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(realpath(__DIR__))),
                        'xml' => array(
                        	'filename' => 'aop-proxy.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_intercept_protected_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodIntercepted');
        $bean->doSomethingProtected();
        $this->assertEquals($bean->something, 'protected');
        $this->assertEquals($bean->called, 'yes');
    }

    /**
     * @test
     */
    public function can_honor_default_values()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodIntercepted');
        $some = 'dsa';
        $result = $bean->anotherMethodWithDefaultValues(array(), $some, new DummyClass);
        $this->assertTrue(empty($result[0]));
        $this->assertEquals($result[1], 'dsa');
        $this->assertTrue($result[2] instanceof DummyClass);
        $this->assertTrue($result[3]);
        $this->assertFalse($result[4]);
        $this->assertNull($result[5]);
        $this->assertEquals($result[6], 'asd');
        $this->assertTrue(is_array($result[7]));
    }
}

/**
 * @Aspect
 */
class MyAspect2
{
    /**
     * @MethodInterceptor(class-expression=ClassSimpleAOPAnnotation3,expression=someProtectedMethod)
     */
	public function methodInterceptorProtected(MethodInvocation $invocation)
	{
        $invocation->getObject()->something = 'protected';
        $invocation->proceed();
	}

	/**
     * @MethodInterceptor(class-expression=ClassSimpleAOPAnnotation3,expression=anotherMethodWithDefaultValues)
     */
	public function methodInterceptor(MethodInvocation $invocation)
	{
        return $invocation->proceed();
	}

	/**
     * @MethodInterceptor(class-expression=ClassSimpleAOPAnnotation3,expression=__.+)
     */
	public function trapMagic(MethodInvocation $invocation)
	{
        return $invocation->proceed();
	}

	/**
     * @MethodInterceptor(class-expression=ClassSimpleAOPAnnotation3,expression=toIgnore)
     */
	public function trapPrivate(MethodInvocation $invocation)
	{
        return $invocation->proceed();
	}
}

class DummyClass
{

}
class ClassSimpleAOPAnnotation3
{
    public $something;
    public $called;

    public function anotherMethodWithDefaultValues(array $a, &$f, DummyClass $g, $b = true, $c = false, $d = null, $e = 'asd', array $h = array())
    {
        return array($a, $f, $g, $b, $c, $d, $e, $h);
    }

    protected function someProtectedMethod()
    {
        $this->called = 'yes';
    }

    private function toIgnore()
    {

    }

    public static function toIgnoreStatic()
    {

    }

    public function doSomethingProtected()
    {
        $this->someProtectedMethod();
    }

    public function __construct()
    {

    }

    public function __destruct()
    {
    }
}


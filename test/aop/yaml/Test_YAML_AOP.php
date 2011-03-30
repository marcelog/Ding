<?php
/**
 * This class will test the YAML aop driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Yaml
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
 * This class will test the YAML aop driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Yaml
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_YAML_AOP extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'aop-yaml-simple.yaml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_intercept_exception_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('exceptionIntercepted');
        $this->assertEquals($bean->thisWillThrowAnException('aSd'), 'aSd');
    }

    /**
     * @test
     */
    public function can_intercept_method_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodIntercepted');
        $this->assertEquals($bean->targetMethod('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_intercept_multiple_methods_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('multipleMethodIntercepted');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_define_global_aspects()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('globalAspectedBeanA');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $bean = $container->getBean('globalAspectedBeanB');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_define_global_pointcuts()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('globalPointcut');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }
}

class ClassSimpleAOPYAML
{
    public function targetMethod($a)
    {
        return 'methodReturnFor' . $a;
    }
}
class ClassSimpleAOPYAMLAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        return 'BEFORE' . $invocation->proceed() . 'AFTER';
    }
}

class ClassSimpleAOPYAML2
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPYAMLGlobalSomething
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPYAMLGlobalSomethingElse
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPYAML3
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPExceptionYAML
{
    public function thisWillThrowAnException()
    {
        throw new Exception('too bad!');
    }
}

class ClassSimpleAOPExceptionYAMLAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        return $args[0];
    }
}

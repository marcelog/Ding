<?php
/**
 * This class will test the ReflectionFactory class.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Reflection
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
use Ding\Reflection\ReflectionFactory;

/**
 * This class will test the ReflectionFactory class.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Reflection
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Reflection extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(),
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

    /**
     * @test
     * Included because of #116
     */
    public function can_parse_annotations()
    {
        $text = <<<TEXT
/**
 * A Comment
 * @Bean(name=blah)
 * @Annotation1 @Annotation2(a=b, d=e)
 * @Annotation3(q=e)@Annotation4(g=h)
 * @Component   ( name = myBean )
 * @InitMethod ( qqq=sss,method=init , asd=dsa )
 * @DestroyMethod   (   method   =  destroy  )
 * @Scope ( value = singleton )
 * @Array ( a = { b, c , d } )
 * @Array2(a={b,c,d})
 * @Quoted( a = "b" , c = "d" )
 * @QuotedArray ( a = { "b" , "c","d"})
 */
TEXT;
        $text2 = <<<TEXT
/** @Some1 */
TEXT;
        $text2 = <<<TEXT
/** @Some1 (a=b,c=d)@Some2 ( a=b)*/
TEXT;
        $container = ContainerImpl::getInstance($this->_properties);
        $reflectionFactory = $container->getBean('dingReflectionFactory');
        $annotations = $reflectionFactory->getAnnotations($text);
        $beanA = array_shift($annotations);
        $an1A = array_shift($annotations);
        $an2A = array_shift($annotations);
        $an3A = array_shift($annotations);
        $an4A = array_shift($annotations);
        $componentA = array_shift($annotations);
        $initMethodA = array_shift($annotations);
        $destroyMethodA = array_shift($annotations);
        $scopeA = array_shift($annotations);
        $arrayA = array_shift($annotations);
        $array2A = array_shift($annotations);
        $quotedA = array_shift($annotations);
        $quotedArrayA = array_shift($annotations);
        $this->assertEquals($componentA->getName(), 'Component');
        $this->assertEquals($beanA->getName(), 'Bean');
        $this->assertEquals($initMethodA->getName(), 'InitMethod');
        $this->assertEquals($destroyMethodA->getName(), 'DestroyMethod');
        $this->assertEquals($scopeA->getName(), 'Scope');
        $this->assertEquals($an1A->getName(), 'Annotation1');
        $this->assertEquals($an2A->getName(), 'Annotation2');
        $this->assertEquals($an3A->getName(), 'Annotation3');
        $this->assertEquals($an4A->getName(), 'Annotation4');
        $this->assertEquals($arrayA->getName(), 'Array');
        $this->assertEquals($array2A->getName(), 'Array2');
        $this->assertEquals($quotedA->getName(), 'Quoted');
        $this->assertEquals($quotedArrayA->getName(), 'QuotedArray');

        $args = $beanA->getArguments();
        $this->assertEquals($args['name'], 'blah');

        $args = $componentA->getArguments();
        $this->assertEquals($args['name'], 'myBean');

        $args = $initMethodA->getArguments();
        $this->assertEquals($args['qqq'], 'sss');
        $this->assertEquals($args['method'], 'init');
        $this->assertEquals($args['asd'], 'dsa');

        $args = $scopeA->getArguments();
        $this->assertEquals($args['value'], 'singleton');

        $args = $destroyMethodA->getArguments();
        $this->assertEquals($args['method'], 'destroy');

        $args = $arrayA->getArguments();
        $this->assertEquals($args['a'], array('b', 'c', 'd'));

        $args = $array2A->getArguments();
        $this->assertEquals($args['a'], array('b', 'c', 'd'));

        $args = $quotedA->getArguments();
        $this->assertEquals($args['a'], 'b');
        $this->assertEquals($args['c'], 'd');
        $args = $quotedArrayA->getArguments();
        $this->assertEquals($args['a'], array('b', 'c', 'd'));

        $annotations = $reflectionFactory->getAnnotations($text2);
        $some1A = array_shift($annotations);
        $this->assertEquals($some1A->getName(), 'Some1');

        $annotations = $reflectionFactory->getAnnotations($text2);
        $some1A = array_shift($annotations);
        $some2A = array_shift($annotations);
        $this->assertEquals($some1A->getName(), 'Some1');
        $this->assertEquals($some2A->getName(), 'Some2');
        $args = $some1A->getArguments();
        $this->assertEquals($args['a'], 'b');
        $this->assertEquals($args['c'], 'd');
        $args = $some2A->getArguments();
        $this->assertEquals($args['a'], 'b');

    }
    /**
     * @test
     */
    public function can_return_nothing_if_no_annotations_driver()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $reflectionFactory = $container->getBean('dingReflectionFactory');
        $result = $reflectionFactory->getClassAnnotations('Test_Reflection');
        $this->assertTrue(empty($result));
        $result = $reflectionFactory->getClassesByAnnotation('link');
        $this->assertTrue(empty($result));
    }
}
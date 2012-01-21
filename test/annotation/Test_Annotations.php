<?php
/**
 * This class will test the Annotations parser.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Annotation
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
use Ding\Reflection\ReflectionFactory;

/**
 * This class will test the Annotations parser.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Annotations extends PHPUnit_Framework_TestCase
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
     */
    public function can_serialize_collection()
    {
        $text = <<<TEXT
/**
 * @Annotation(name=value)
 */
TEXT;
        $container = ContainerImpl::getInstance($this->_properties);
        $parser = $container->getBean('dingAnnotationParser');
        $annotations = $parser->parse($text);
        $serialized = serialize($annotations);
    }

    /**
     * @test
     * @expectedException \Ding\Annotation\Exception\AnnotationException
     */
    public function cannot_return_unknown_annotation()
    {
        $text = <<<TEXT
/**
 * @Annotation(name=value)
 */
TEXT;
        $container = ContainerImpl::getInstance($this->_properties);
        $parser = $container->getBean('dingAnnotationParser');
        $annotations = $parser->parse($text);
        $annotation = $annotations->getSingleAnnotation('unsetannotation');
    }

    /**
     * @test
     * @expectedException \Ding\Annotation\Exception\AnnotationException
     */
    public function cannot_return_unknown_value()
    {
        $text = <<<TEXT
/**
 * @Something(name=value)
 */
TEXT;
        $container = ContainerImpl::getInstance($this->_properties);
        $parser = $container->getBean('dingAnnotationParser');
        $annotations = $parser->parse($text);
        $annotation = $annotations->getSingleAnnotation('something');
        $value = $annotation->getOptionValues('unknown');
    }

    /**
     * @test
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
        $parser = $container->getBean('dingAnnotationParser');
        $annotations = $parser->parse($text);
        $beanA = $annotations->getSingleAnnotation('bean');
        $an1A = $annotations->getSingleAnnotation('annotation1');
        $an2A = $annotations->getSingleAnnotation('annotation2');
        $an3A = $annotations->getSingleAnnotation('annotation3');
        $an4A = $annotations->getSingleAnnotation('annotation4');
        $componentA = $annotations->getSingleAnnotation('Component');
        $initMethodA = $annotations->getSingleAnnotation('InitMethod');
        $destroyMethodA = $annotations->getSingleAnnotation('DestroyMethod');
        $scopeA = $annotations->getSingleAnnotation('Scope');
        $arrayA = $annotations->getSingleAnnotation('Array');
        $array2A = $annotations->getSingleAnnotation('Array2');
        $quotedA = $annotations->getSingleAnnotation('Quoted');
        $quotedArrayA = $annotations->getSingleAnnotation('QuotedArray');
        $this->assertEquals($componentA->getName(), 'component');
        $this->assertEquals($beanA->getName(), 'bean');
        $this->assertEquals($initMethodA->getName(), 'initmethod');
        $this->assertEquals($destroyMethodA->getName(), 'destroymethod');
        $this->assertEquals($scopeA->getName(), 'scope');
        $this->assertEquals($an1A->getName(), 'annotation1');
        $this->assertEquals($an2A->getName(), 'annotation2');
        $this->assertEquals($an3A->getName(), 'annotation3');
        $this->assertEquals($an4A->getName(), 'annotation4');
        $this->assertEquals($arrayA->getName(), 'array');
        $this->assertEquals($array2A->getName(), 'array2');
        $this->assertEquals($quotedA->getName(), 'quoted');
        $this->assertEquals($quotedArrayA->getName(), 'quotedarray');

        $this->assertEquals($beanA->getOptionValues('name'), array('blah'));
        $this->assertEquals($componentA->getOptionValues('name'), array('myBean'));
        $this->assertEquals($initMethodA->getOptionValues('qqq'), array('sss'));
        $this->assertEquals($initMethodA->getOptionValues('method'), array('init'));
        $this->assertEquals($initMethodA->getOptionValues('asd'), array('dsa'));
        $this->assertEquals($scopeA->getOptionValues('value'), array('singleton'));
        $this->assertEquals($destroyMethodA->getOptionValues('method'), array('destroy'));
        $this->assertEquals($arrayA->getOptionValues('a'), array('b', 'c', 'd'));
        $this->assertEquals($array2A->getOptionValues('a'), array('b', 'c', 'd'));

        $args = $quotedA->getOptions();
        $this->assertEquals($quotedA->getOptionSingleValue('a'), 'b');
        $this->assertEquals($quotedA->getOptionSingleValue('c'), 'd');
        $this->assertEquals($quotedArrayA->getOptionValues('a'), array('b', 'c', 'd'));

        $annotations = $parser->parse($text2)->getAnnotations('some1');
        $some1A = array_shift($annotations);
        $this->assertEquals($some1A->getName(), 'some1');

        $annotations = $parser->parse($text2);
        $an = $annotations->getAnnotations('some1');
        $some1A = array_shift($an);
        $an = $annotations->getAnnotations('some2');
        $some2A = array_shift($an);
        $this->assertEquals($some1A->getName(), 'some1');
        $this->assertEquals($some2A->getName(), 'some2');
        $this->assertEquals($some1A->getOptionValues('a'), array('b'));
        $this->assertEquals($some1A->getOptionValues('c'), array('d'));
        $this->assertEquals($some2A->getOptionValues('a'), array('b'));
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
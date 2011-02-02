<?php
/**
 * This driver will search for @Resource setter methods.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
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
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Factory\Exception\BeanFactoryException;

use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will search for @Resource setter methods.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class AnnotationResourceDriver implements IAfterDefinitionListener
{
    /**
     * Holds current instance.
     * @var AnnotationResourceDriver
     */
    private static $_instance = false;

    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        $beanClass = $bean->getClass();
        $annotations = ReflectionFactory::getClassAnnotations($beanClass);
        $properties = $bean->getProperties();
        foreach ($annotations as $method => $annotations) {
            if ($method == 'class') {
                continue;
            }
            if (strpos($method, 'set') !== 0) {
                continue;
            }
            $propName = lcfirst(substr($method, 3));
            foreach ($annotations as $annotation) {
                if ($annotation->getName() == 'Resource') {
                    $properties[] = new BeanPropertyDefinition(
                        $propName, BeanPropertyDefinition::PROPERTY_BEAN, $propName
                    );
                }
            }
        }
        $bean->setProperties($properties);
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return AnnotationResourceDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new AnnotationResourceDriver;
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct()
    {
    }
}
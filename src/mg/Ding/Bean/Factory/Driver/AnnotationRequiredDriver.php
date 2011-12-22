<?php
/**
 * This driver will search for @Required setter methods.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
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
namespace Ding\Bean\Factory\Driver;

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Reflection\IReflectionFactory;

/**
 * This driver will search for @Required setter methods.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class AnnotationRequiredDriver implements IAfterDefinitionListener, IReflectionFactoryAware
{
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        $beanClass = $bean->getClass();
        $annotations = $this->reflectionFactory->getClassAnnotations($beanClass);
        $props = $bean->getProperties();
        foreach ($annotations as $method => $annotations) {
            if ($method == 'class') {
                continue;
            }
            if (strpos($method, 'set') !== 0) {
                continue;
            }
            $propName = lcfirst(substr($method, 3));
            foreach ($annotations as $annotation) {
                if ($annotation->getName() == 'Required') {
                    if (!isset($props[$propName])) {
                        throw new BeanFactoryException('Missing @Required property: ' . $method);
                    }
                }
            }
        }
        return $bean;
    }
}
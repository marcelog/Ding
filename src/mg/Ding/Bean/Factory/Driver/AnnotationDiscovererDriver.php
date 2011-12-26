<?php
/**
 * This driver will search for annotations.
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

use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Reflection\IReflectionFactory;
use Ding\Reflection\IReflectionFactoryAware;

/**
 * This driver will search for annotations.
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
class AnnotationDiscovererDriver
    implements IAfterConfigListener, IReflectionFactoryAware
{
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->_reflectionFactory = $reflectionFactory;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterConfigListener::afterConfig()
     */
    public function afterConfig()
    {
        foreach ($this->_directories as $dir) {
            $classesPerFile = $this->_reflectionFactory->getClassesFromDirectory($dir);
			/*
		 	 * First include the file so we can actually get the doc comment (by
         	 * calling \ReflectionClass::getDocComment()). This will allow us to
         	 * actually get the annotations, and we also need this so we can
         	 * know about @Configuration and @ListensOn earlier on.
         	 */
            foreach ($classesPerFile as $file => $classes) {
                include_once $file;
                foreach ($classes as $class) {
                    $this->_reflectionFactory->getClassAnnotations($class);
                }
            }
        }
    }

    public function __construct(array $directories)
    {
        $this->_directories = $directories;
    }
}

<?php
/**
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
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
namespace Ding\Bean\Factory\Driver;

use Ding\MVC\Http\HttpUrlMapper;

use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\IBeanFactory;

/**
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
class MVCAnnotationDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var MVCAnnotationDriver
     */
    private static $_instance = false;

    /**
     * Will call HttpUrlMapper::addAnnotatedController to add new mappings
     * from the @Controller annotated classes. Also, creates a new bean
     * definition for every one of them.
     *
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        foreach (ReflectionFactory::getClassesByAnnotation('Controller') as $controller) {
            $name = 'Controller' . microtime(true);
            $beanDef = new BeanDefinition($name);
            $beanDef->setClass($controller);
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $url = ReflectionFactory::getClassAnnotations($controller);
            if (!isset($url['class']['RequestMapping'])) {
                continue;
            }
            $url = $url['class']['RequestMapping']->getArguments();
            if (!isset($url['url'])) {
                continue;
            }
            $url = $url['url'];
            $factory->setBeanDefinition($name, $beanDef);
            HttpUrlMapper::addAnnotatedController($url, $name);
        }
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return MVCAnnotationDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new MVCAnnotationDriver;
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
<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Lifecycle
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

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
declare(ticks=1);
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg')),
            ini_get('include_path'),
        )
    )
);
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\BeanDefinition;

error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class ComponentA
{
    public function __construct()
    {
    }
}


class MyLifecycler implements
    IBeforeDefinitionListener, IAfterDefinitionListener,
    IBeforeCreateListener, IAfterCreateListener,
    IBeforeAssembleListener, IAfterAssembleListener
{
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition $bean = null)
    {
        echo "beforeDefinition called\n";
        return $bean; // mandatory
    }

    public function afterDefinition(IBeanFactory $factory, BeanDefinition $bean)
    {
        echo "afterDefinition called\n";
        return $bean; // mandatory
    }

    public function beforeCreate(IBeanFactory $factory, BeanDefinition $beanDefinition)
    {
        echo "beforeCreate called\n";
    }

    public function afterCreate(IBeanFactory $factory, $bean, BeanDefinition $beanDefinition)
    {
        echo "afterCreate called\n";
    }

    public function beforeAssemble(IBeanFactory $factory, $bean, BeanDefinition $beanDefinition)
    {
        echo "beforeAssemble called\n";
    }

    public function afterAssemble(IBeanFactory $factory, $bean, BeanDefinition $beanDefinition)
    {
        echo "afterAssemble called\n";
    }

    public function __construct()
    {
    }
}

////////////////////////////////////////////////////////////////////////////////
try
{
    $properties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
            'factory' => array(
                'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                ),
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'dummy'),
            	'annotation' => array('impl' => 'dummy'),
            	'bdef' => array('impl' => 'dummy'),
              	'beans' => array('impl' => 'dummy'),
                'aspect' => array('impl' => 'dummy')
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    $bean = $a->getBean('lifecycler');
    $bean = $a->getBean('dummyBean');
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////

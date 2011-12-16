<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  global
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
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
class Bean
{
    public function onBean2Created($data = null)
    {
        echo "bean2: Bean2Created: " . get_class($data) . "\n";
    }
}

class Bean3
{
    public function onBean2Created($data = null)
    {
        echo "bean3: Bean2Created: " . get_class($data) . "\n";
    }
}

class Bean4
{
    public function onBean2Created($data = null)
    {
        echo "bean4: Bean2Created: " . get_class($data) . "\n";
    }
}

/**
 * @Configuration
 */
class Config
{
    /**
     * @Bean(class=Bean4)
     * @Scope(value=singleton)
     * @ListensOn(value=bean2Created)
     */
    public function someBean()
    {
        return new Bean4;
    }
}

class Bean2 implements \Ding\Container\IContainerAware
{
    private $_container;
    public function setContainer(\Ding\Container\IContainer $container)
    {
        $this->_container = $container;
    }

    public function initMethod()
    {
        $this->_container->eventDispatch('bean2Created', $this);
    }
}

$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
           'bdef' => array(
            	'xml' => array('filename' => 'beans.xml'),
            	'yaml' => array('filename' => 'beans.yaml'),
               'annotation' => array('scanDir' => array(__DIR__))
            ),
        ),
    )
);
$a = \Ding\Container\Impl\ContainerImpl::getInstance($properties);
$bean = $a->getBean('bean2');
////////////////////////////////////////////////////////////////////////////////

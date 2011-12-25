<?php
/**
 * Example using Resources
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Resources
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
use Ding\Resource\IResourceLoaderAware;
use Ding\Resource\Impl\URLResource;
use Ding\Resource\Impl\IncludePathResource;
use Ding\Resource\IResourceLoader;

error_reporting(E_ALL);
ini_set('display_errorrs', 1);

/**
 * @Configuration
 */
class MyConfigProvider
{
    /**
     * @Bean(class=SomeClass)
     * @Scope(value=singleton)
     */
    public function aBean()
    {
        return new SomeClass();
    }
    public function __construct()
    {
    }
}

class SomeClass implements IResourceLoaderAware
{
    private $_resourceLoader;

    public function setResourceLoader(IResourceLoader $resourceLoader)
    {
        $this->_resourceLoader = $resourceLoader;
    }

    public function loadSomething($file)
    {
        return fread($this->_resourceLoader->getResource($file)->getStream(), 1000);
    }

    public function __construct()
    {
        // You can use resource right from your own code.
        $resource = new URLResource('http://www.google.com');
        var_dump(fread($resource->getStream(), 1000));
    }
}
// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => __DIR__ . '/../log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'annotation' => array('scanDir' => array(__DIR__))
            ),
        ),
    )
);
$container = ContainerImpl::getInstance($properties);
$bean = $container->getBean('aBean');
var_dump($bean->loadSomething('/bin/login'));
var_dump($bean->loadSomething('includepath:///Ding/Autoloader/Autoloader.php'));
var_dump($bean->loadSomething('http://www.google.com'));
echo "Now type something, since we've opened the resource php://stdin\n";
var_dump($bean->loadSomething('php://stdin'));
var_dump($bean->loadSomething('zip://test.zip#example.php'));

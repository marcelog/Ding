<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage ConfigurationAnnotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg'))
        )
    )
);

/**
 * This is our bean.
 * @author SomeAuthor
 */
class MyBean
{
    private $_someProperty;

    public function aMethod()
    {
        echo "Init\n";
    }

    public function bMethod()
    {
        echo "Destroy\n";
    }

    public function setSomeProperty($value)
    {
        $this->_someProperty = $value;
    }

    public function getSomeProperty()
    {
        return $this->_someProperty;
    }
    public function __construct()
    {

    }
}
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\Reflection\ReflectionFactory;

// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
        ),
        // You can configure the cache for the bean definition, the beans, and the proxy definitions.
        // Other available implementations: zend, file, dummy, and memcached.
    	'cache' => array(
            'proxy' => array('impl' => 'file', 'directory' => '/tmp/Ding/cache/proxy'),
            'bdef' => array('impl' => 'file', 'directory' => '/tmp/Ding/cache/bdef'),
            'annotations' => array('impl' => 'file', 'directory' => '/tmp/Ding/cache/annotations'),
        	'beans' => array('impl' => 'dummy')
        )
    )
);
$container = ContainerImpl::getInstance($properties);
$bean = $container->getBean('someBean');
var_dump($bean->getSomeProperty());
var_dump(ReflectionFactory::getClassesByAnnotation('author'));

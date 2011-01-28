<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Basic
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

class MyInnerBean
{
    public function __construct()
    {

    }
}

class MyOtherInnerBean
{
    public function __construct()
    {

    }
}

/**
 * This is our bean.
 */
class MyBean
{
    private $_someProperty;
    private $_somePropertyInnerBean;

    public function setSomeProperty($value)
    {
        $this->_someProperty = $value;
    }

    public function getSomeProperty()
    {
        return $this->_someProperty;
    }

    public function setSomePropertyInnerBean($value)
    {
        $this->_somePropertyInnerBean = $value;
    }

    public function getSomePropertyInnerBean()
    {
        return $this->_somePropertyInnerBean;
    }

    public function __construct($value)
    {
        echo 'Injected by constructor: ';
        var_dump($value);
    }
}
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;

// Here you configure the container, its subcomponents, drivers, etc.
$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array( // Both of these drivers are optional. They are both included just for the thrill of it.
                'xml' => array('filename' => 'beans.xml'),
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
            // These properties will be used by the container when instantiating the beans, see beans.xml
            'properties' => array(
                'user.name' => 'nobody',
            )
        ),
        // You can configure the cache for the bean definition, the beans, and the proxy definitions.
        // Other available implementations: zend, file, dummy, and memcached.
    	'cache' => array(
            'proxy' => array('impl' => 'dummy'),
            'bdef' => array('impl' => 'dummy'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
$container = ContainerImpl::getInstance($properties);
$bean = $container->getBean('myBeanName');
var_dump($bean->getSomePropertyInnerBean());
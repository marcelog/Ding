<?php
/**
 * This driver will make the setter injection.
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
 */
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This driver will make the setter injection.
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
class SetterInjectionDriver implements IBeforeAssembleListener
{
    /**
     * Cache property setters names.
     * @var array[]
     */
    private $_propertiesNameCache;

    /**
     * Holds current instance.
     * @var SetterInjectionDriver
     */
    private static $_instance = false;

    /**
     * This will return the property value from a definition.
     *
     * @param BeanPropertyDefinition $property Property definition.
     *
     * @return mixed
     */
    private function _loadProperty(IBeanFactory $factory, BeanPropertyDefinition $property)
    {
        if ($property->isBean()) {
            return $factory->getBean($property->getValue());
        }
        if ($property->isArray()) {
            $value = array();
            foreach ($property->getValue() as $k => $v) {
                $value[$k] = $this->_loadProperty($factory, $v);
            }
            return $value;
        }
        if ($property->isCode()) {
            return eval($property->getValue());
        }
        return $property->getValue();
    }

    public function beforeAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        foreach ($beanDefinition->getProperties() as $property) {
            $propertyName = $property->getName();
            if (isset($this->_propertiesNameCache[$propertyName])) {
                $methodName = $this->_propertiesNameCache[$propertyName];
            } else {
                $methodName = 'set' . ucfirst($propertyName);
                $this->_propertiesNameCache[$propertyName] = $methodName;
            }
            $rClass = ReflectionFactory::getClass($beanDefinition->getClass());
            if ($rClass->hasMethod($methodName)) {
                $bean->$methodName($this->_loadProperty($factory, $property));
            }
        }
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return SetterInjectionDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new SetterInjectionDriver;
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
        $this->_propertiesNameCache = array();
    }
}
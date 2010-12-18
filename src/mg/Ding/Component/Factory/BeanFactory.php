<?php
/**
 * Generic bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Component
 * @subpackage Factory
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Component\Factory;

use Ding\Component\BeanDefinition;
use Ding\Component\BeanPropertyDefinition;
use Ding\Aspect\Proxy;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\InterceptorDefinition;

/**
 * Generic bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Component
 * @subpackage Factory
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
abstract class BeanFactory
{
    /**
     * Bean definitions already known.
     * @var BeanDefinition[]
     */
    private $_beanDefs;

    /**
     * Beans already instantiated.
     * @var object[]
     */
    private $_beans;
    
    /**
     * This will assembly a bean (inject dependencies, loading other needed
     * beans in the way).
     * 
     * @param object         $bean Where to call 'setXXX' methods.
     * @param BeanDefinition $def  Bean definition, used to get needed 
     * properties.
     * 
     * @throws BeanFactoryException
     * @return void
     */
    private function _assemble($bean, BeanDefinition $def)
    {
        foreach ($def->getProperties() as $property) {
            $method = $this->_getSetterFor(
                $def->getClass(), $property->getName()
            );
            switch ($property->getType())
            {
            case BeanPropertyDefinition::PROPERTY_BEAN:
                $value = $this->getBean($property->getValue());
                break; 
            case BeanPropertyDefinition::PROPERTY_SIMPLE:
                $value = $property->getValue();
                break; 
            default:
                throw new BeanFactoryException('Invalid property type');
            }
            $method->invoke($bean, $value);
        }
    }
    
    /**
     * Returns the method (a reflection method) used for di'ing stuff.
     * 
     * @param string $class Class name.
     * @param string $name  Property name. If name is 'name', then this will
     * return a method named 'setName'.
     * 
     * @return \ReflectionMethod
     */
    private function _getSetterFor($class, $name)
    {
        $rClass = new \ReflectionClass($class);
        return $rClass->getMethod('set' . ucfirst($name));
    }
    
    /**
     * @throws BeanFactoryException
     */
    private function _createBean(BeanDefinition $beanDefinition)
    {
        $beanClass = $beanDefinition->getClass();
        if ($beanDefinition->hasAspects()) {
            $bean = Proxy::create($beanClass);
            foreach ($beanDefinition->getAspects() as $aspectDefinition) {
                $aspect = $this->getBean($aspectDefinition->getBeanName());

                $refAspect = new \ReflectionObject($aspect); 
                $refObject = new \ReflectionObject($bean);
                
                $advice = $refAspect->getMethod($aspectDefinition->getAdvice());
                $joinpoint = $refObject->getMethod($aspectDefinition->getPointcut());
                
                $interceptor = new InterceptorDefinition(
                    $joinpoint, $advice, $aspect, $aspectDefinition
                );
                $bean::setInterceptor($interceptor);
            }
        } else {
            /* @todo change this to a clone */
            $bean = new $beanClass;
        }
        try
        {
            $this->_assemble($bean, $beanDefinition);
        } catch(\ReflectionException $exception) {
            throw new BeanFactoryException('DI Error', 0, $exception);
        }
        return $bean;
    }
    
    /**
     * Override this one with your own implementation.
     *
     * @param string $beanName Bean to get definition for.
     * 
     * @return BeanDefinition
     */
    public abstract function getBeanDefinition($beanName);

    /**
     * Returns a bean.
     * 
     * @param string $beanName Bean name.
     * 
     * @throws BeanFactoryException
     * @return object
     */
    public function getBean($beanName)
    {
        $ret = false;
        if (!isset($this->_beanDefs[$beanName])) {
            $beanDefinition = $this->getBeanDefinition($beanName);
            if (!$beanDefinition) {
                throw new BeanFactoryException('Unknown bean: ' . $beanName);
            }
            $this->_beanDefs[$beanName] = $beanDefinition;
        } else {
            $beanDefinition = $this->getBeanDefinition($beanName);
        }
        switch ($beanDefinition->getScope())
        {
        case BeanDefinition::BEAN_PROTOTYPE:
            $ret = $this->_createBean($beanDefinition);
            break;
        case BeanDefinition::BEAN_SINGLETON:
            if (!isset($this->_beans[$beanName])) {
                $ret = $this->_createBean($beanDefinition);
                $this->_beans[$beanName] = $ret;
            } else {
                $ret = $this->_beans[$beanName];
            }
            break;
        default:
            throw new BeanFactoryException('Invalid bean scope');
        }
        return $ret;
    }
    
    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_beans = array();
        $this->_beanDefs = array();
    }
}
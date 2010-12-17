<?php
namespace Ding;

/**
 * Container implementation.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

/**
 * Container implementation.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class ContainerImpl implements IContainer
{
	private $_beanList = false;
	private $_beans = false;
	private static $_containerInstance = false;

	private function _getBeanList()
	{
		return $this->_beanList;
	}

	private function _di($bean, BeanDefinition $def)
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
	            throw new ContainerException('Invalid property type');
	        }
            $method->invoke($bean, $value);
		}
	}
	
	private function _getSetterFor($class, $name)
	{
        $rClass = new \ReflectionClass($class);
        return $rClass->getMethod('set' . ucfirst($name));
	}
	
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
                    $joinpoint, $advice, $aspect
                );
    	        $bean::setInterceptor($interceptor);
            }
        } else {
            /* @todo change this to a clone */
            $bean = new $beanClass;
        }
    	try
    	{
    	    $this->_di($bean, $beanDefinition);
    	} catch(\ReflectionException $exception) {
    	    throw new ContainerException('DI Error', 0, $exception);
    	}
    	return $bean;
	}
	
	public function getBean($beanName)
	{
	    $ret = false;
		$beanList = $this->_getBeanList();
		$beanDefinition = $beanList->getBean($beanName);
		if (!$beanDefinition) {
			throw new ContainerException('Unknown bean: ' . $beanName);
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
		    throw new ContainerException('Invalid bean scope');
		}
		return $ret;
	}

	public static function getInstance($filename)
	{
		return
		    self::$_containerInstance === false
		    ? new ContainerImpl($filename)
			: self::$_containerInstance
		;
	}

	protected function __construct($filename)
	{
	    $this->_beans = array();
		$list = new BeanList($filename);
		$list->load();
		$this->_beanList = $list;
		self::$_containerInstance = $this;
	}
}

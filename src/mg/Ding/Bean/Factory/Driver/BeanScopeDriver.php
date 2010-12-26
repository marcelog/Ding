<?php
/**
 * This driver will instantiate the bean.
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

use Ding\Bean\Lifecycle\Exception\BeanLifecycleException;
use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\BeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will instantiate the bean.
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
class BeanScopeDriver implements ILifecycleListener
{
    /**
     * Beans already instantiated.
     * @var object[]
     */
    private $_beans;

    /**
     * Holds current instance.
     * @var BeanAnnotationDriver
     */
    private static $_instance = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition &$bean)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     */
    public function beforeDefinition($beanName, BeanDefinition &$bean = null)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeCreate()
     */
    public function beforeCreate(BeanDefinition $beanDefinition)
    {
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterCreate()
     */
    public function afterCreate(&$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeAssemble()
     */
    public function beforeAssemble(&$bean, BeanDefinition $beanDefinition)
    {
        if ($bean != null) {
            return $bean;
        }
        $name = $beanDefinition->getName();
        switch ($beanDefinition->getScope())
        {
        case BeanDefinition::BEAN_PROTOTYPE:
            return $bean;
        case BeanDefinition::BEAN_SINGLETON:
            if (!isset($this->_beans[$name])) {
                return $bean;
            } else {
                return $this->_beans[$name];
            }
            break;
        default:
            throw new BeanFactoryException('Invalid bean scope');
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterAssemble()
     */
    public function afterAssemble(&$bean, BeanDefinition $beanDefinition)
    {
        $name = $beanDefinition->getName();
        if (!isset($this->_beans[$name])) {
            $this->_beans[$name] = $bean;
        }
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::destruct()
     */
    public function destruct($bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     * 
     * @return BeanCreateDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance === false) {
            $ret = new BeanScopeDriver($options);
            self::$_instance = $ret;
        } else {
            $ret = self::$_instance;
        }
        return $ret;
    }
    
    /**
     * Constructor.
     *
     * @param array $options Optional options.
     * 
     * @return void
     */
    private function __construct(array $options)
    {
        
    }
}
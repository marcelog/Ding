<?php
/**
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

use Ding\MVC\Http\HttpUrlMapper;

use Ding\Bean\Lifecycle\ILifecycleListener;
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
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class MVCAnnotationDriver implements ILifecycleListener
{
    /**
     * Holds current instance.
     * @var MVCAnnotationDriver
     */
    private static $_instance = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeConfig()
     */
    public function beforeConfig(IBeanFactory $factory)
    {

    }

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
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeCreate()
     */
    public function beforeCreate(IBeanFactory $factory, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterCreate()
     */
    public function afterCreate(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * Annotates the given bean with the annotations found in the class and
     * every method.
     *
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     *
     * @return BeanDefinition
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition &$bean = null)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeAssemble()
     */
    public function beforeAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterAssemble()
     */
    public function afterAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
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
     * @return MVCAnnotationDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance === false) {
            $ret = new MVCAnnotationDriver($options);
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
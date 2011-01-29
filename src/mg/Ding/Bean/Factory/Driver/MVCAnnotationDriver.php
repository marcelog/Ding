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

use Ding\Bean\Lifecycle\IAfterConfigListener;
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
class MVCAnnotationDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var MVCAnnotationDriver
     */
    private static $_instance = false;

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
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return MVCAnnotationDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance !== false) {
            return self::$_instance;
        }
        self::$_instance = new MVCAnnotationDriver;
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct()
    {

    }
}
<?php
namespace Ding\Bean\Provider;

use Ding\Bean\BeanConstructorArgumentDefinition;

use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\IBeanDefinitionProvider;

class Core implements IBeanDefinitionProvider
{
    /**
     * Container options.
     * @var string[]
     */
    protected $options;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::get()
     */
    public function getBeanDefinition($name)
    {
        $bean = null;
        switch ($name)
        {
        case 'dingAutoloaderCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getAutoloaderCacheInstance');
            break;
        case 'dingAnnotationsCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getAnnotationsCacheInstance');
            break;
        case 'dingBeanCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getBeansCacheInstance');
            break;
        case 'dingDefinitionsCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getDefinitionsCacheInstance');
            break;
        case 'dingProxyCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getProxyCacheInstance');
            break;
        case 'dingAspectCache':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Cache\Locator\CacheLocator');
            $bean->setFactoryMethod('getAspectCacheInstance');
            break;
        case 'dingAspectManager':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Aspect\AspectManager');
            $bean->setProperties(array(
                new BeanPropertyDefinition(
                	'cache', BeanPropertyDefinition::PROPERTY_BEAN,
                	'dingAspectCache'
                )
            ));
            break;
        case 'dingXmlBeanDefinitionProvider':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Provider\Xml');
            $bean->setArguments(array(
                new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE,
                    $this->options['bdef']['xml']
                )
            ));
            break;
        case 'dingAnnotationBeanDefinitionProvider':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Provider\Annotation');
            $bean->setArguments(array(
                new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE,
                    $this->options['bdef']['annotation']
                )
            ));
            $bean->setProperties(array(
                new BeanPropertyDefinition(
                	'cache', BeanPropertyDefinition::PROPERTY_BEAN,
                	'dingAnnotationsCache'
                )
            ));
            break;
        case 'dingYamlBeanDefinitionProvider':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Provider\Yaml');
            $bean->setArguments(array(
                new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE,
                    $this->options['bdef']['yaml']
                )
            ));
            break;
        case 'dingAspectCallDispatcher':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Aspect\Interceptor\DispatcherImpl');
            break;
        case 'dingAnnotationInitDestroyMethodDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\AnnotationInitDestroyMethodDriver');
            break;
        case 'dingAnnotationAspectDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\AnnotationAspectDriver');
            break;
        case 'dingMvcAnnotationDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\MvcAnnotationDriver');
            break;
        case 'dingAnnotationRequiredDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\AnnotationRequiredDriver');
            break;
        case 'dingAnnotationResourceDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\AnnotationResourceDriver');
            break;
        case 'dingPropertiesDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\PropertiesDriver');
            $bean->setArguments(array(
                new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE,
                    $this->options['properties']
                )
            ));
            break;
        case 'dingLifecycleManager':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Lifecycle\BeanLifecycleManager');
            break;
        case 'dingMethodInjectionDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\MethodInjectionDriver');
            break;
        case 'dingMessageSourceDriver':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Bean\Factory\Driver\MessageSourceDriver');
            break;
        case 'dingReflectionFactory':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Reflection\ReflectionFactory');
            $bean->setArguments(array(
                new BeanConstructorArgumentDefinition(
                    BeanConstructorArgumentDefinition::BEAN_CONSTRUCTOR_VALUE,
                    isset($this->options['bdef']['annotation'])
                )
            ));
            $bean->setProperties(array(
                new BeanPropertyDefinition(
                	'cache', BeanPropertyDefinition::PROPERTY_BEAN,
                	'dingAnnotationsCache'
                )
            ));
            break;
        case 'dingProxyFactory':
            $bean = new BeanDefinition($name);
            $bean->setClass('\Ding\Aspect\Proxy');
            $bean->setProperties(array(
                new BeanPropertyDefinition(
                	'cache', BeanPropertyDefinition::PROPERTY_BEAN,
                	'dingProxyCache'
                )
            ));
            break;
        default:
            break;
        }
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getByClass()
     */
    public function getBeanDefinitionByClass($class)
    {
        return null;
    }

    public function __construct($options = array())
    {
        $this->options = $options;
    }
}
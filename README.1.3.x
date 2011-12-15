Changes
-------
* More mature code. Lots of internal code & interface cleanups :)
* The container constructor no longer instantiates its drivers directly.
* The drivers are all beans now (yeeey!).
* Xml, Yaml, and Annotation are now "providers" instead of "drivers".
* ErrorHandler, ShutdownHandler, and SignalHandler are now run by default, but
you dont need any other configurations. You can hook in them by listening for
events: "dingError", "dingShutdown", "dingSignal". See the updated examples.
Note: The signal handler will only be enabled if sapi is cli or cgi.
* BeforeDefinition in the lifecycle is gone.
* BeforeConfig in the lifecycle is gone.
* IBeanDefinitionProvider now replaces IBeforeDefinition.
* IBeanFactory is gone.
* ReflectionFactory and Proxy factory have instance methods and variables instead of static ones.
* New IReflectionFactoryAware.
* All beans that implement IBeanDefinitionProvider are automatically registered
in the container. 
* New IContainer::registerBeanDefinitionProvider.
* IContainer::getLogger() is gone.
* IContainer::setBeanDefinition() is gone.
* IContainer::setBean() is gone.
* All beans that implement IAspectProvider and IPointcutProvider are automatically
registered in the AspectManager.
* New BeanDefinitionProvider: Core, that provides all basic core beans for bootstrap.
* Implemented FileCache::flush().
* HttpDispatcher will now call the actions with their arguments, as passed from the
request.
* PropertiesHelper is now stripped from all logic. PropertiesDriver will do the job now.
* Integrated drivers into core:
  * LoggerAware
  * AspectManagerAware
  * ResourceLoaderAware
  * ResourceDriver
  * ContainerAware
  * LifecycleAware
  * BeanNameAware
  * DependsOn
  * ReflectionFactoryAware
* The lifecycle is now:
  * beforeConfig
  * afterConfig
  * afterDefinition
  * beforeCreate
  * beforeAssemble
  * afterCreate
* MVCException changed to MvcException.
* Namespace Ding\MVC changed to Ding\Mvc
* Namespace Ding\Helpers\TCP changed to Ding\Helpers\Tcp
* Namespace Ding\Helpers\PAGI changed to Ding\Helpers\Pagi
* Namespace Ding\Helpers\PAMI changed to Ding\Helpers\Pami
* The TimerHelper was removed.

Migrating Mvc from 1.1.x to > 1.3.x
-----------------------------------
If you are using the Mvc from versions < 1.3.x, your actions should look like:

public function someAction(array $arguments = array())
{
}

Since version 1.3.x, the HttpDispatcher included will no longer send an array as
the argument for actions, but will try to map request variables according to the
signature of the method for the selected action.

Suppose you're interested in $arguments having arguments 'arg1', and 'arg2', then
your action's signature should now be changed to:

public function someAction($arg1, $arg2)
{
}

You can also have optional arguments:

public function someAction($arg1, $arg2, $arg3 = 'default')
{
}

Ding will populate the arguments according to the argument names used
in $_GET and $_POST. A Ding\MVC\Exception\MVCException will be thrown
if a non optional argument is not supplied in the request.


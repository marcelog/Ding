<?php
/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Container;

use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\Lifecycle\BeanLifecycle;

/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface IContainer extends IBeanFactory
{
    /**
     * Register a shutdown (destroy-method) method for a bean.
     *
     * @param object $bean   Bean to call.
     * @param string $method Method to call.
     *
     * @see Ding\Container.IContainer::registerShutdownMethod()
     *
     * @return void
     */
    public function registerShutdownMethod($bean, $method);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeConfigListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterConfigListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeDefinitionListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterDefinitionListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeCreateListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterCreateListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeAssembleListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterAssembleListener(ILifecycleListener $listener);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param ILifecycleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeDestructionListener(ILifecycleListener $listener);
}

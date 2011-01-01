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
}

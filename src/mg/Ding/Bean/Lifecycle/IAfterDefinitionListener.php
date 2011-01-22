<?php
/**
 * Interface for a AfterDefinition lifecycle event.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Lifecycle
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Lifecycle;

use Ding\Bean\BeanDefinition;
use Ding\Bean\Factory\IBeanFactory;

/**
 * Interface for a AfterDefinition lifecycle event.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Lifecycle
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IAfterDefinitionListener extends ILifecycleListener
{
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean);
}
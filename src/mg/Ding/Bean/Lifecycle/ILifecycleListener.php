<?php
/**
 * Lifecycle listener interface.
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
 * Lifecycle listener interface.
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
interface ILifecycleListener
{
    public function beforeConfig(IBeanFactory $factory);
    public function afterConfig(IBeanFactory $factory);

    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition &$bean = null);
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean);

    public function beforeCreate(IBeanFactory $factory, BeanDefinition $beanDefinition);
    public function afterCreate(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition);

    public function beforeAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition);
    public function afterAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition);

    public function destruct($bean, BeanDefinition $beanDefinition);
}

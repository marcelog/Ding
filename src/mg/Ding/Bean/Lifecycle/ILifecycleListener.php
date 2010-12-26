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
use Ding\Bean\Factory\BeanFactory;

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
    public function beforeDefinition(BeanFactory $factory, $beanName, BeanDefinition &$bean = null);
    public function afterDefinition(BeanFactory $factory, BeanDefinition &$bean);

    public function beforeCreate(BeanFactory $factory, BeanDefinition $beanDefinition);
    public function afterCreate(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition);

    public function beforeAssemble(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition);
    public function afterAssemble(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition);

    public function destruct($bean, BeanDefinition $beanDefinition);
    public static function getInstance(array $options);
}

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
    public function beforeDefinition($beanName, BeanDefinition &$bean = null);
    public function afterDefinition(BeanDefinition &$bean);

    public function beforeCreate(BeanDefinition $beanDefinition);
    public function afterCreate(&$bean, BeanDefinition $beanDefinition);

    public function beforeAssemble(&$bean, BeanDefinition $beanDefinition);
    public function afterAssemble(&$bean, BeanDefinition $beanDefinition);

    public function destruct($bean, BeanDefinition $beanDefinition);
    public static function getInstance(array $options);
}

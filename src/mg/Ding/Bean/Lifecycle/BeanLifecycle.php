<?php
/**
 * Definition for a bean lifecycle.
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

/**
 * Definition for a bean lifecycle.
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
abstract class BeanLifecycle
{
    /**
     * The bean is in the process of being defined. Typically by a final
     * backend, like xml or mysql, etc.
     * @var integer
     */
    const BeforeDefinition = 0;

    /**
     * The bean has just been defined and is about to be created. The definition
     * will not change after this step.
     * @var integer
     */
    const AfterDefinition = 1;
    const BeforeAssemble = 2;
    const AfterAssemble = 3;
    const BeforeDestruction = 4;
    public static function initiate($beanName, BeanFactory $factory, array $lifecyclers)
    {
    }
}

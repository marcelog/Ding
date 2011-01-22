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

    /**
     * Before calling factory::createBean()
     * @var integer
     */
    const BeforeCreate = 2;

    /**
     * After calling factory::createBean()
     * @var integer
     */
    const AfterCreate = 3;

    /**
     * Before calling factory::assemble()
     * @var integer
     */
    const BeforeAssemble = 4;

    /**
     * After calling factory::assemble()
     * @var integer
     */
    const AfterAssemble = 5;

    /**
     * When the container is shutting down
     * @var integer
     */
    const BeforeDestruction = 6;

    /**
     * When the container is about to configure itself and its subsytems.
     * @var integer
     */
    const BeforeConfig = 7;

    /**
     * Right after configuring everything, ready to be used by the user.
     * @var integer
     */
    const AfterConfig = 8;
}

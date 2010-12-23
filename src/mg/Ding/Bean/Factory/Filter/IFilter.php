<?php
/**
 * Way too simple definition for a filter.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Filter
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Factory\Filter;

/**
 * Way too simple definition for a filter.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Filter
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IFilter
{
    /**
     * Applies a filter.
     * 
     * @param mixed $input What you want to filte.r
     * 
     * @return mixed
     */
    public function apply($input);
}

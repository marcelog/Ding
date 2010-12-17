<?php
namespace Ding;

/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface IContainer
{
    /**
     * Returns a bean, by name. The bean might be a proxy, if aspected.
     * 
     * @param string $bean Bean name.
     * 
     * @result object
     */
	public function getBean($bean);
}

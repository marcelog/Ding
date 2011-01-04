<?php
/**
 * This will map actions to controllers.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\MVC;

/**
 * This will map actions to controllers.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface IMapper
{
    /**
     * Returns a Controller suitable to handle the given action.
     * @param IAction $action
     * 
     * @return IController
     */
    public function map(Action $action);
    
    /**
     * Configures the mapper with a given map.
     *
     * @param array $map [0] => Action, [1] => Controller
     * 
     * @return void
     */
    public function setMap(array $map);
}
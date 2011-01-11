<?php
/**
 * Implement this interface in your own shutdown handler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ShutdownHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\ShutdownHandler;

/**
 * Implement this interface in your own shutdown handler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ShutdownHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IShutdownHandler
{
    /**
     * Your shutdown handler.
     *
     * @return void
     */
    public function handleShutdown();
}
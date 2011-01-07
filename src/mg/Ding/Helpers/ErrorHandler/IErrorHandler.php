<?php
/**
 * Implement this interface in your own error handler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ErrorHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\ErrorHandler;

/**
 * Implement this interface in your own error handler.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage ErrorHandler
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IErrorHandler
{
    /**
     * Your error handler.
     *
     * @param ErrorInfo $error The triggered error, notice, etc.
     *
     * @return void
     */
    public function handleError(ErrorInfo $error);
}
<?php
/**
 * Interface for an aspect dispatcher.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Aspect\Interceptor;

use Ding\Aspect\MethodInvocation;
/**
 * Interface for an aspect dispatcher.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IDispatcher
{ 
    public function invokeException(MethodInvocation $invocation);
    public function invoke(MethodInvocation $invocation);
}
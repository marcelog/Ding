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
    /**
     * The proxy will call this method when an aspected method throws an
     * exception.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     * 
     * @return void
     */
    public function invokeException(MethodInvocation $invocation);

    /**
     * The proxy will call this method when an aspected method is called.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     * 
     * @return void
     */
    public function invoke(MethodInvocation $invocation);
}

<?php
/**
 * Your aspect classes must implement this interface in order to work with
 * this framework. (In the case you're interested in running advices
 * after/before a method execution).
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
 * Your aspect classes must implement this interface in order to work with
 * this framework. (In the case you're interested in running advices
 * after/before a method execution).
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
interface IMethodInterceptor
{
    /**
     * Will be called before a method execution. Use proceed() in order to
     * continue the chain and you will be run afterwards, if everything worked
     * well.
     * 
     * @param MethodInvocation $invocation In chained aspects, this will be
     * the invocation for the next aspect after you call proceed(). Use
     * getOriginalInvocation() to access the original aspected method call.
     * 
     * @see MethodInvocation::getOriginalInvocation()
     * @return void
     */
    public function invoke(MethodInvocation $invocation);
}
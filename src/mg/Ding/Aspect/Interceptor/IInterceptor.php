<?php
/**
 * Base interceptor.
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
 * Base interceptor.
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
interface IInterceptor
{
    /**
     * Will be called whenever necessary.
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
<?php
namespace Ding\Aspect\Interceptor;

use Ding\Aspect\MethodInvocation;

interface IExceptionInterceptor
{
    public function invokeException(MethodInvocation $invocation);
}